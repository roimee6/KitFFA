<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\tool;

use CortexPE\Commando\args\TextArgument;
use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\command\sub\TargetArgument;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Sudo extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "sudo",
            "Fait executer une commande ou parler un joueur"
        );

        $this->setPermission("pocketmine.group.operator");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($args["joueur"] === "@a") {
            Util::allSelectorExecute($sender, $this->getName(), $args);
            return;
        }

        $player = Util::getPlayer(array_shift($args));

        if ($player instanceof Player) {
            $sudo = trim(implode(" ", $args));

            if ($sudo[0] === "/") {
                Base::getInstance()->getServer()->dispatchCommand($player, substr($sudo, 1));
                $sender->sendMessage(Util::PREFIX . "La commande indiqué a été faite par le joueur");
            } else {
                $player->chat($sudo);
                $sender->sendMessage(Util::PREFIX . "Le message indiqué a été envoyé par le joueur");
            }
        } else {
            $sender->sendMessage(Util::PREFIX . "Le joueur indiqué n'est pas connecté sur le serveur");
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(1, new TextArgument("commande"));
    }
}