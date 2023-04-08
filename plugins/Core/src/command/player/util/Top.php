<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use NCore\command\sub\OptionArgument;
use NCore\handler\Cache;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class Top extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "top",
            "Envoie la liste des meilleurs joueurs"
        );

        $this->setAliases(["classement"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $i = 1;

        $page = !isset($args["page"]) ? 1 : $args["page"];
        $format = "§7{COUNT}. §9{KEY} §8(§f{VALUE}§8)";

        $top = self::getPlayersTopList($args["opt"]);
        $response = Util::arrayToPage($top, $page, 10);

        $sender->sendMessage(Util::PREFIX . self::getTopName($args["opt"]) . " §f(Page §9#" . $page . "§f/§9" . $response[0] . "§f)");

        foreach ($response[1] as $value) {
            $sender->sendMessage(str_replace(["{KEY}", "{VALUE}", "{COUNT}"], [$value[0], $value[1], (($page - 1) * 10) + $i], $format));
            $i++;
        }
    }

    public static function getPlayersTopList(string $category): array
    {
        $leaderboard = Cache::$players[$category] ?? [];

        $array = [];
        $result = [];

        foreach ($leaderboard as $key => $value) {
            $upper = Cache::$players["upper_name"][$key] ?? $key;
            $array[$upper] = $value;
        }

        arsort($array);

        foreach ($array as $key => $value) {
            $result[] = [
                $key,
                $value
            ];
        }
        return $result;
    }

    public static function getTopName(string $category): string
    {
        return match ($category) {
            "killstreak" => "Joueurs avec les plus gros §9killstreak",
            "elo" => "Joueurs ayant le plus d'§9elo",
            "death" => "Joueurs ayant le plus de §9morts",
            default => "Joueurs ayant le plus de §9kills"
        };
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new OptionArgument("opt", ["killstreak", "kill", "elo", "death"]));
        $this->registerArgument(1, new IntegerArgument("page", true));
    }
}