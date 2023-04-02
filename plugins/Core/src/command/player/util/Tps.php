<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class Tps extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "tps",
            "Affiche les tps du serveur en temps réel"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $server = Base::getInstance()->getServer();
        $bar = "§l§8-----------------------";

        $sender->sendMessage($bar);
        $sender->sendMessage(Util::PREFIX . "Tps Actuel: §9" . $server->getTicksPerSecond() . " §f(§9" . $server->getTickUsage() . "%§f)");
        $sender->sendMessage(Util::PREFIX . "Tps en Moyenne: §9" . $server->getTicksPerSecondAverage() . " §f(§9" . $server->getTickUsageAverage() . "%§f)");
        $sender->sendMessage($bar);
    }

    protected function prepare(): void
    {
    }
}