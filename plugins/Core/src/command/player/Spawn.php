<?php /** @noinspection PhpUnused */

namespace NCore\command\player;

use CortexPE\Commando\BaseCommand;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Spawn extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "spawn",
            "Permet au point de base du serveur"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if ($session->inCooldown("combat")) {
                $sender->sendMessage(Util::PREFIX . "Vous ne pouvez pas aller au spawn en combat");
                return;
            }

            Util::refresh($sender, true, true);
            $sender->sendMessage(Util::PREFIX . "Vous venez de retourner au spawn");
        }
    }

    protected function prepare(): void
    {
    }
}