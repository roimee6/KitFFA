<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\handler\Cache;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class Alias extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "alias",
            "Permet de voir tous les comptes d'un joueur"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $target = strtolower($args["joueur"]);

        if (!isset(Cache::$players["upper_name"][$target])) {
            $sender->sendMessage(Util::PREFIX . "Ce joueur ne s'est jamais connecté au serveur (verifiez bien les caractères)");
            return;
        }

        $alias = $this->getPlayerAliasByName($target);
        $bar = "§l§8-----------------------";

        if (count($alias) === 0) {
            $sender->sendMessage(Util::PREFIX . "Le joueur §9" . $target . " §fne possède aucun double compte lié à son ip, did etc..");
            return;
        }

        $sender->sendMessage($bar);
        $sender->sendMessage(Util::PREFIX . "Liste de compte lié au compte §9" . $target);

        foreach ($alias as $username) {
            $sender->sendMessage("§f- §9" . $username);
        }

        $sender->sendMessage($bar);
    }

    private function getPlayerAliasByName(string $name): array
    {
        $file = Util::getFile("players/" . $name);
        $result = [];

        foreach (Cache::$config["saves"] as $column) {
            $ip = $file->get($column, []);

            foreach (Cache::$players[$column] as $key => $value) {
                $similar = array_intersect_assoc($value, $ip);

                if (count($similar) > 0 && $key !== $name) {
                    $result[] = $key . " §f- Depuis son §9" . $column;
                }
            }
        }
        return $result;
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(0, new RawStringArgument("joueur"));
    }
}