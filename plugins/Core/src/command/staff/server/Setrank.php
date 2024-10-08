<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\command\sub\OptionArgument;
use NCore\command\sub\TargetArgument;
use NCore\handler\Cache;
use NCore\handler\RankAPI;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Setrank extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "setrank",
            "Ajoute un grade à un joueur"
        );

        $this->setPermission("pocketmine.group.operator");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!RankAPI::existRank($args["grade"])) {
            $sender->sendMessage(Util::PREFIX . "Ce rang n'existe pas ou vous n'avez pas respecté les majuscules");
            return;
        }

        if (($target = Util::getPlayer($args["joueur"])) instanceof Player) {
            Session::get($target)->removeCooldown("kit");
            $target = $target->getName();
        } else {
            $target = strtolower($args["joueur"]);

            if (!isset(Cache::$players["upper_name"][$target])) {
                $sender->sendMessage(Util::PREFIX . "Ce joueur ne s'est jamais connecté au serveur (verifiez bien les caractères)");
                return;
            }
        }

        RankAPI::setRank($target, $args["grade"]);
        $sender->sendMessage(Util::PREFIX . "Vous venez d'ajouter le rang §9" . $args["grade"] . " §fà un joueur (§9" . $target . "§f)");
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(0, new RawStringArgument("joueur"));
        $this->registerArgument(1, new OptionArgument("grade", array_keys(Cache::$config["ranks"])));
    }
}