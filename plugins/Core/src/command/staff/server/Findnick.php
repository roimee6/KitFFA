<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class Findnick extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "findnick",
            "Trouve le joueur possedant le nick de son choix"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $nick = $args["pseudo"];
        $found = null;

        foreach (Base::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (strtolower($player->getDisplayName()) === strtolower($nick)) {
                if ($player->getName() !== $player->getDisplayName()) {
                    $found = $player->getName();
                    break;
                }
            }
        }

        if (is_null($found)) {
            $sender->sendMessage(Util::PREFIX . "Aucun joueur ne possede ce nick actuellement connecté sur le serveur");
        } else {
            $sender->sendMessage(Util::PREFIX . "Le pseudo §9" . $nick . " §fest le nick du joueur §9" . $found);
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("pseudo"));
    }
}