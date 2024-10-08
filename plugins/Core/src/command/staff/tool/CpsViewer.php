<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\tool;

use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class CpsViewer extends BaseCommand
{
    public static array $cpsViewer = [];

    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "cpsview",
            "Permet de voir les cps d'un joueur en temps réel"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $name = $args["joueur"] ?? "delete";
        $target = Util::getPlayer($name);

        if ($name === "delete") {
            unset(self::$cpsViewer[$sender->getName()]);
            $sender->sendMessage(Util::PREFIX . "Vous venez de désactiver le cpsviewer !");
            return;
        } else if (!$target instanceof Player) {
            $sender->sendMessage(Util::PREFIX . "Le joueur indiqué n'est pas connecté sur le serveur");
            return;
        }

        self::$cpsViewer[$sender->getName()] = $target->getName();
        $sender->sendMessage(Util::PREFIX . "Vous verrez désormais les cps en temps réel du joueur §9" . $target->getName() . " §f!");
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur", true));
    }
}