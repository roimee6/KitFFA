<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Ping extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "ping",
            "Récupére la latence entre un joueur et le serveur"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!isset($args["joueur"])) {
            if ($sender instanceof Player) {
                $sender->sendMessage(Util::PREFIX . "Vous possèdez §9" . $sender->getNetworkSession()->getPing() . " §fde ping");
            }
        } else {
            $target = Util::getPlayer($args["joueur"]);

            if (!$target instanceof Player) {
                if ($sender instanceof Player) {
                    $sender->sendMessage(Util::PREFIX . "Vous possèdez §9" . $sender->getNetworkSession()->getPing() . " §fde ping");
                }
                return;
            }
            $sender->sendMessage(Util::PREFIX . "Le joueur §9" . $target->getName() . "§f possède §9" . $target->getNetworkSession()->getPing() . "§f de ping");
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur", true));
    }
}