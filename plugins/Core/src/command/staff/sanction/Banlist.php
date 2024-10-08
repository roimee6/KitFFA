<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\sanction;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use NCore\handler\Cache;
use NCore\handler\SanctionAPI;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class Banlist extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "banlist",
            "Affiche la liste des joueurs banni du serveur"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $list = $this->getBanList();
        $format = "§9{KEY}§f, raison: §9{REASON} §8(§f{TIME}§8)";

        $i = 1;
        $page = $args["page"] ?? 1;

        $response = Util::arrayToPage($list, $page, 10);
        $sender->sendMessage(Util::PREFIX . "Liste des joueurs banni du serveur §f(Page §9#" . $page . "§f/§9" . $response[0] . "§f)");

        foreach ($response[1] as $value) {
            $sender->sendMessage("§7" . (($page - 1) * 10) + $i . ". " . str_replace(["{KEY}", "{REASON}", "{TIME}"], [$value[3], $value[2], $value[1]], $format));
            $i++;
        }
    }

    private function getBanList(): array
    {
        $result = [];

        foreach (Cache::$bans as $key => $value) {
            $time = $value[1];

            if ($time > time()) {
                if (strlen(strval($key)) > 15 || str_contains(strval($key), ".")) {
                    continue;
                }

                $value[1] = SanctionAPI::format($time - time());
                $value[3] = $key;
                $result[$key] = $value;
            } else {
                unset(Cache::$bans[$key]);
            }
        }
        return $result;
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new IntegerArgument("page", true));
    }
}