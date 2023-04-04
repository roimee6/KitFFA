<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemFactory;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Spectate extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "spectate",
            "Change votre mode de jeu en spectateur"
        );

        $this->setAliases(["spec"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);
            $item = $sender->getInventory()->getItemInHand();

            if ($session->inCooldown("combat")) {
                $sender->sendMessage(Util::PREFIX . "Vous ne pouvez pas vous mettre en mode spectateur en combat");
                return;
            }

            if ($item->getCustomName() === "§r" . Util::PREFIX . "Spectateur §9§l«" && $item->getId() === 507) {
                $sender->getInventory()->setItemInHand(ItemFactory::getInstance()->get(501)->setCustomName("§r" . Util::PREFIX . "Spectateur §9§l«"));
            } else if ($item->getCustomName() === "§r" . Util::PREFIX . "Spectateur §9§l«" && $item->getId() === 501) {
                $sender->getInventory()->setItemInHand(ItemFactory::getInstance()->get(507)->setCustomName("§r" . Util::PREFIX . "Spectateur §9§l«"));
            }

            if ($sender->getGamemode() === GameMode::SPECTATOR()) {
                $sender->setGamemode(GameMode::SURVIVAL());

                if (!$session->data["staff_mod"][0]) {
                    Util::refresh($sender, true, true);
                    Util::giveItems($sender);
                } else {
                    $sender->setAllowFlight(true);
                }

                $sender->sendMessage(Util::PREFIX . "Vous n'êtes plus en mode spectateur");
            } else {
                $sender->setGamemode(GameMode::SPECTATOR());
                $sender->sendMessage(Util::PREFIX . "Vous êtes désormais en mode spectateur");
            }
        }
    }

    protected function prepare(): void
    {
    }
}