<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\BaseCommand;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class StaffChat extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "staffchat",
            "Active le staffchat"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if ($session->data["staff_chat"]) {
                $sender->sendMessage(Util::PREFIX . "Vous venez de désactiver le staffchat");
                $session->data["staff_chat"] = false;
            } else {
                $sender->sendMessage(Util::PREFIX . "Vous venez d'activer le staffchat");
                $session->data["staff_chat"] = true;
            }
        }
    }

    protected function prepare(): void
    {
    }
}