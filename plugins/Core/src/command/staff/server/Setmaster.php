<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\handler\Cache;
use NCore\handler\RankAPI;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Setmaster extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "setmaster",
            "Permet d'ajouter le grade master à un joueur pendant 2J"
        );

        $this->setPermission("pocketmine.group.operator");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $username = strtolower($args["joueur"]);
        $player = Util::getPlayer($username);

        $days = $args["jours"] ?? 2;

        if ($player instanceof Player) {
            $session = Session::get($player);

            $time = $this->getNewTime($days, $session->data["master"] ?? 0);
            $session->data["master"] = $time;
        } else {
            if (isset(Cache::$players["upper_name"][$username])) {
                $file = Util::getFile("players/" . $username);
                $time = $this->getNewTime($days, $file->get("master", 0));

                $file->set("master", $time);
                $file->save();
            }
        }

        RankAPI::setRank($username, "master", false);
        $sender->sendMessage(Util::PREFIX . "Vous venez de définir le joueur §9" . $username . " §fcomme booster");
    }

    private function getNewTime(int $days, int $time): int
    {
        return ($time > time() ? $time : time()) + ((24 * 60 * 60) * $days);
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(0, new RawStringArgument("joueur"));
        $this->registerArgument(1, new IntegerArgument("jours", true));
    }
}