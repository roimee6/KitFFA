<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Rekit extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "rekit",
            "Récupère un nouveau kit et des nouvelles pots"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if ($session->inCooldown("combat")) {
                $sender->sendMessage(Util::PREFIX . "Vous ne pouvez pas rekit en combat");
                return;
            }

            Util::refresh($sender);
            Util::giveKit($sender);

            $sender->sendMessage(Util::PREFIX . "Vous venez de rekit");
        }
    }

    protected function prepare(): void
    {
    }
}