<?php /** @noinspection PhpUnused */

namespace NCore\command\player;

use CortexPE\Commando\BaseCommand;
use NCore\task\repeat\TournamentTask;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Join extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "join",
            ""
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            TournamentTask::$players[] = $sender;
        }
    }

    protected function prepare(): void
    {
    }
}