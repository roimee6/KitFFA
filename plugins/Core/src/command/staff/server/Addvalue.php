<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\command\sub\OptionArgument;
use NCore\command\sub\TargetArgument;
use NCore\handler\Cache;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Addvalue extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "addvalue",
            "Ajoute n'importe quel valeur dans les data d'un joueur"
        );

        $this->setPermission("pocketmine.group.operator");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $data = $args["valeur"];
        $amount = intval($args["montant"]);

        if ($args["joueur"] === "@a") {
            Util::allSelectorExecute($sender, $this->getName(), $args);
            return;
        }

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

        $sender->sendMessage(Util::PREFIX . "Vous venez d'ajouter §9" . $amount . " §f" . $data . " au joueur §9" . $target);
        Addvalue::addValue($sender->getName(), $target, $data, $amount);
    }

    public static function addValue(string $staff, string $key, string $column, int $value): void
    {
        $player = Util::getPlayer($key);

        if ($player instanceof Player) {
            Session::get($player)->addValue($column, $value);

            if ($value > 0) {
                $player->sendMessage(Util::PREFIX . "Le staff §9" . $staff . " §fvient de vous ajouter §9" . $value . " §f" . $column);
            } else {
                $player->sendMessage(Util::PREFIX . "Le staff §9" . $staff . " §fvient de vous retirer §9" . $value . " §f" . $column);
            }
        } else {
            $file = Util::getFile("players/" . $key);

            if ($file->getAll() !== []) {
                $file->set($column, $file->get($column) + $value);
                $file->save();
            }
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(0, new RawStringArgument("joueur"));
        $this->registerArgument(1, new IntegerArgument("montant"));
        $this->registerArgument(2, new OptionArgument("valeur", ["kill", "elo", "killstreak", "death"]));
    }
}