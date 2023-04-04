<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Leave extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "leave",
            "Permet d'abandonner un combat ou d'aller au spawn"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $ev = new PlayerDeathEvent($sender, [], 0, "");
            $ev->call();
        }
    }

    protected function prepare(): void
    {
    }
}