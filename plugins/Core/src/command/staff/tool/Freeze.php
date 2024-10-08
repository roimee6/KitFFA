<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\tool;

use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Freeze extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "freeze",
            "Rend immobile un joueur"
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

        if ($target->isImmobile()) {
            $target->setImmobile(false);

            $target->sendMessage(Util::PREFIX . "Vous venez d'être unfreeze, vous pouvez désormais bouger");
            $sender->sendMessage(Util::PREFIX . "Vous venez de unfreeze le joueur §9" . $target->getName());
        } else {
            $target->setImmobile();

            $target->sendMessage(Util::PREFIX . "Vous venez d'être freeze, vous ne pouvez plus bouger");
            $sender->sendMessage(Util::PREFIX . "Vous venez de freeze le joueur §9" . $target->getName());
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
    }
}