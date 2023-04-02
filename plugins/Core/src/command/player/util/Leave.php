<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Leave extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "leave",
            "Permet d'abandonner un combat"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if (!$session->inCooldown("combat")) {
                $sender->sendMessage(Util::PREFIX . "Vous devez être en combat pour faire abandonner un combat");
                return;
            }

            $data = $session->getCooldownData("combat");

            $damager = $data[1];
            $damager = Util::getPlayer($damager);

            if ($damager instanceof Player) {
                $sender->kill();
            } else {
                Util::refresh($sender, true);
            }
        }
    }

    protected function prepare(): void
    {
    }
}