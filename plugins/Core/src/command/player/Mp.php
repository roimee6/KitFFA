<?php /** @noinspection PhpUnused */

namespace NCore\command\player;

use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\command\sub\TargetArgument;
use NCore\handler\SanctionAPI;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\sound\ClickSound;

class Mp extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "mp",
            "Envoie un message à un ou plusieurs joueurs"
        );

        $this->setAliases(["msg", "tell"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if ($args["joueur"] === "@a") {
                Util::allSelectorExecute($sender, $this->getName(), $args);
                return;
            } else if ($session->inCooldown("mute")) {
                $sender->sendMessage(Util::PREFIX . "Vous êtes mute, temps restant: §9" . SanctionAPI::format($session->getCooldownData("mute")[0] - time()));
                return;
            }
            $player = Util::getPlayer(array_shift($args));

            if ($player instanceof Player) {
                Base::getInstance()->getLogger()->info("[MP] [" . $sender->getName() . " » " . $player->getName() . "] " . implode(" ", $args));

                $session->data["reply"] = $player->getName();
                Session::get($player)->data["reply"] = $sender->getName();

                foreach ([$player, $sender] as $players) {
                    $players->sendMessage("§9[§fMP§9] §9[§f" . $sender->getName() . " " . Util::PREFIX . $player->getName() . "§9] §f" . implode(" ", $args));
                    $players->broadcastSound(new ClickSound());
                }
            } else {
                $sender->sendMessage(Util::PREFIX . "Le joueur indiqué n'est pas connecté sur le serveur");
            }
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(1, new TextArgument("message"));
    }
}