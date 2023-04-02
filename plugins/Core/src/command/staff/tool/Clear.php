<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\tool;

use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Clear extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "clear",
            "Supprime les items de son inventaire ou d'un joueur"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $target = $args["joueur"] ?? $sender->getName();
        $target = Util::getPlayer($target);

        if (!$target instanceof Player) {
            $sender->sendMessage(Util::PREFIX . "Le joueur indiqué n'est pas connecté sur le serveur");
            return;
        }

        $target->getInventory()->clearAll();
        $target->getArmorInventory()->clearAll();

        if ($target->getName() === $sender->getName()) {
            $sender->sendMessage(Util::PREFIX . "Vous venez de supprimé tous les items de votre inventaire");
        } else {
            $sender->sendMessage(Util::PREFIX . "Vous venez de supprimé tous les items de l'inventaire de §9" . $target->getName());
            $target->sendMessage(Util::PREFIX . "Tous les items de votre inventaire vient d'être supprimé par §9" . $sender->getName());
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur", true));
    }
}