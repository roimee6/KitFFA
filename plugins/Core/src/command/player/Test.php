<?php /** @noinspection PhpUnused */

namespace NCore\command\player;

use CortexPE\Commando\BaseCommand;
use NCore\Session;
use NCore\task\repeat\TournamentTask;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Test extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "test",
            ""
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
       TournamentTask::$current = true;
    }

    protected function prepare(): void
    {
    }
}