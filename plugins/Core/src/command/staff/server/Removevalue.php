<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\command\sub\OptionArgument;
use NCore\command\sub\TargetArgument;
use NCore\handler\Cache;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Removevalue extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "removevalue",
            "Supprime n'importe quel valeur dans les data d'un joueur"
        );

        $this->setPermission("pocketmine.group.operator");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $data = $args["valeur"];
        $amount = intval($args["montant"]);

        if (($target = Util::getPlayer($args["joueur"])) instanceof Player) {
            $target = $target->getName();
        } else {
            $target = strtolower($args["joueur"]);

            if (!isset(Cache::$players["upper_name"][$target])) {
                $sender->sendMessage(Util::PREFIX . "Ce joueur ne s'est jamais connecté au serveur (verifiez bien les caractères)");
                return;
            }
        }

        if (0 > $amount) {
            $sender->sendMessage(Util::PREFIX . "Le montant que vous avez inscrit est invalide");
            return;
        }

        $sender->sendMessage(Util::PREFIX . "Vous venez de retirer §9" . $amount . " §f" . $data . " au joueur §9" . $target);
        Addvalue::addValue($sender->getName(), $target, $data, -$amount);
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(0, new RawStringArgument("joueur"));
        $this->registerArgument(1, new IntegerArgument("montant"));
        $this->registerArgument(2, new OptionArgument("valeur", ["kill", "elo", "killstreak", "death"]));
    }
}