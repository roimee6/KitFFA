<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\tool;

use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Vanish extends BaseCommand
{
    public static array $vanish = [];

    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "vanish",
            "Disparaît aux yeux des autres joueurs"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $item = $sender->getInventory()->getItemInHand();

            if (in_array($sender->getName(), Vanish::$vanish)) {
                foreach (Base::getInstance()->getServer()->getOnlinePlayers() as $player) {
                    $player->showPlayer($sender);
                }

                unset(Vanish::$vanish[array_search($sender->getName(), Vanish::$vanish)]);
                $sender->sendMessage(Util::PREFIX . "Vous êtes désormais visible aux yeux des autres joueurs");

                if ($item->getCustomName() === "§r" . Util::PREFIX . "Vanish §9§l«") {
                    $sender->getInventory()->setItemInHand(ItemFactory::getInstance()->get(ItemIds::DYE, 8)->setCustomName("§r" . Util::PREFIX . "Vanish §9§l«"));
                }
            } else {
                foreach (Base::getInstance()->getServer()->getOnlinePlayers() as $player) {
                    if ($player->hasPermission("staff.group") || $player->getName() === $sender->getName()) {
                        continue;
                    }

                    $player->hidePlayer($sender);
                }

                Vanish::$vanish[] = $sender->getName();
                $sender->sendMessage(Util::PREFIX . "Vous êtes désormais invisible aux yeux des autres joueurs");

                if ($item->getCustomName() === "§r" . Util::PREFIX . "Vanish §9§l«") {
                    $sender->getInventory()->setItemInHand(ItemFactory::getInstance()->get(ItemIds::DYE, 10)->setCustomName("§r" . Util::PREFIX . "Vanish §9§l«"));
                }
            }
        }
    }

    protected function prepare(): void
    {
    }
}