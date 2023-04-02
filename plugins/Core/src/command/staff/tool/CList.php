<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\tool;

use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use Webmozart\PathUtil\Path;

class CList extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "list",
            "Récupére la liste des joueurs connectés au serveur"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $players = Base::getInstance()->getServer()->getOnlinePlayers();

        $players = array_map(function (Player $player): string {
            return $player->getName();
        }, $players);

        $path = Path::join(Base::getInstance()->getServer()->getDataPath(), "players");
        $count = count(glob($path . "/*")) + 1;

        $list = implode("§f, §9", $players);
        $sender->sendMessage(Util::PREFIX . "Voici la liste des joueurs connectés sur le serveur actuellement (§9" . count($players) . "§f)\n§9" . $list . "\n§9" . $count . " §fjoueurs uniques se sont déjà connectés au serveur");
    }

    protected function prepare(): void
    {
    }
}