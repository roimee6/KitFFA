<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\sanction;

use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\handler\Cache;
use NCore\handler\discord\Discord;
use NCore\handler\discord\EmbedBuilder;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Unmute extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "unmute",
            "Redonne la parole à un joueur"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $target = Util::getPlayer($args["joueur"]);

        if (!$target instanceof Player) {
            $sender->sendMessage(Util::PREFIX . "Le joueur indiqué n'est pas connecté sur le serveur");
            return;
        }
        $session = Session::get($target);

        if (!$session->inCooldown("mute")) {
            $sender->sendMessage(Util::PREFIX . "Le joueur §9" . $target->getName() . " §fn'est pas mute");
            return;
        }

        $session->removeCooldown("mute");

        $sender->sendMessage(Util::PREFIX . "Vous venez de unmute §9" . $target->getName());
        $target->sendMessage(Util::PREFIX . "Vous venez d'être unmute par §9" . $sender->getName());

        $embed = new EmbedBuilder();
        $embed->setDescription("**Unmute**\n\n**Joueur**\n" . $target->getName() . "\n\n*Unmute par le staff: " . $sender->getName() . "*");
        $embed->setColor(5635925);
        Discord::send($embed, Cache::$config["sanction_webhook"]);
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
    }
}