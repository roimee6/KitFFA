<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Stats extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "stats",
            "Récupere ses informations ou celle d'un autre joueur"
        );

        $this->setAliases(["info"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $username = strtolower($args["joueur"] ?? $sender->getName());
            $target = Util::getPlayer($username);

            if (!$target instanceof Player) {
                $sender->sendMessage(Util::PREFIX . "Ce joueur n'est pas connecté au serveur");
                return;
            }

            $bar = "§l§8-----------------------";

            $session = Session::get($target);
            $data = $session->data;

            $league = $session->getLeague();
            $percentage = $session->getPercentage();

            $sender->sendMessage($bar);
            $sender->sendMessage("§9[§f" . $league . "§9] [§f" . ucfirst(strtolower($data["rank"])) . "§9] §f- §9" . $target->getName());
            $sender->sendMessage("§9Elo: §f" . $data["elo"]);
            $sender->sendMessage("§9Pourcentage: §f" . $percentage);
            $sender->sendMessage("§9Kills: §f" . $data["kill"]);
            $sender->sendMessage("§9Morts: §f" . $data["death"]);
            $sender->sendMessage("§9Killstreak: §f" . $data["killstreak"]);
            $sender->sendMessage($bar);
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur", true));
    }
}