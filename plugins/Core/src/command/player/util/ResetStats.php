<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\handler\RankAPI;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class ResetStats extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "resetstats",
            "Réinitialise ses statistiques (elo, pourcentage, ligue, kill..)"
        );

        $this->setAliases(["rs"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if (!RankAPI::hasRank($sender, "master")) {
                $sender->sendMessage(Util::PREFIX . "Vous n'avez pas la permission de faire cela");
                return;
            }

            $session->setValue("elo", 0);
            $session->setValue("kill", 0);
            $session->setValue("death", 0);
            $session->setValue("killstreak", 0);

            $sender->sendMessage(Util::PREFIX . "Vous venez de réinitialiser vos statistiques");
        }
    }

    protected function prepare(): void
    {
    }
}