<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class StaffList extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "stafflist",
            "Donne la liste de staff connectés au serveur"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $players = Base::getInstance()->getServer()->getOnlinePlayers();
        $array = [];

        foreach ($players as $player) {
            if ($player->hasPermission("staff.group")) {
                $staffmod = Session::get($player)->data["staff_mod"][0];
                $array[] = $player->getName() . ($staffmod ? " (§fModeStaff§9)" : "");
            }
        }

        $list = implode("§f, §9", $array);
        $sender->sendMessage(Util::PREFIX . "Voici la liste des staffs connectés sur le serveur actuellement (§9" . count($array) . "§f)\n§9" . $list);
    }

    protected function prepare(): void
    {
    }
}