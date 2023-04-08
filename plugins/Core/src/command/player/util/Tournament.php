<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use jojoe77777\FormAPI\CustomForm;
use NCore\Base;
use NCore\handler\Cache;
use NCore\handler\RankAPI;
use NCore\handler\SanctionAPI;
use NCore\Session;
use NCore\task\repeat\TournamentTask;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\Position;

class Tournament extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "tournoi",
            "Lance un tournoi ou rejoins un tournoi en cours"
        );

        $this->setAliases(["event"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            if (!TournamentTask::$current) {
                $session = Session::get($sender);

                if (!RankAPI::hasRank($sender, "master")) {
                    $sender->sendMessage(Util::PREFIX . "Aucun tournoi en cours ! Demandez à un joueur gradé d'en lancer un !");
                    return;
                } else if ($session->inCooldown("tournament")) {
                    $sender->sendMessage(Util::PREFIX . "Vous avez déjà lancé un tournoi il n'y a pas si longtemps que ça.. Attendez: §9" . SanctionAPI::format($session->getCooldownData("tournament")[0] - time()));
                    return;
                }

                $this->createTournamentForm($sender);
            } else {
                $already = false;

                if (Session::get($sender)->inCooldown("combat")) {
                    $sender->sendMessage(Util::PREFIX . "Vous ne pouvez pas rejoindre de tournoi en combat");
                    return;
                } else if (count(TournamentTask::$players) >= TournamentTask::$setting["max"]) {
                    $sender->sendMessage(Util::PREFIX . "L'hôte du tournoi a limité le nombre de joueurs maximum à §9" . TournamentTask::$setting["max"] . "§f, vous ne pourrez donc pas pvp..");
                    $already = true;
                }

                $setting = TournamentTask::$setting;
                $config = Cache::$config["tournaments"][$setting["map"]];

                if (TournamentTask::$status === 0 && !$already) {
                    foreach (TournamentTask::$players as $key => $player) {
                        if ($player->getXuid() === $sender->getXuid()) {
                            unset(TournamentTask::$players[$key]);
                            $already = true;
                        }
                    }

                    TournamentTask::$players[] = $sender;
                    $message = Util::PREFIX . "Le joueur §9" . $sender->getName() . " §fvient de rejoindre le tournoi §9" . $setting["name"] . " §f(§9" . count(TournamentTask::$players) . "§f/§9" . $setting["max"] . "§f)";

                    if ($already) {
                        $sender->sendMessage($message);
                    } else {
                        Base::getInstance()->getServer()->broadcastMessage($message);
                    }
                }

                $world = Base::getInstance()->getServer()->getWorldManager()->getWorldByName($setting["map"]);
                $data = explode(":", $config["spectate"]);

                $position = new Position(floatval($data[0]), floatval($data[1]), floatval($data[2]), $world);
                $sender->teleport($position);

                $sender->setGamemode(GameMode::SURVIVAL());
                Util::refresh($sender);
            }
        }
    }

    private function createTournamentForm(Player $player): void
    {
        $config = Cache::$config["tournaments"];

        $form = new CustomForm(function (Player $player, mixed $data) use ($config) {
            if (!is_array($data)) {
                return;
            }

            $name = $data[1];
            $map = array_keys($config)[$data[2]];
            $kit = array_keys(Kit::getKits())[$data[3]];
            $potion = $data[4];
            $count = $data[5];
            $wait = $data[6];
            $max = $data[7];

            TournamentTask::$current = true;

            TournamentTask::$setting = [
                "name" => $name,
                "potion" => $potion,
                "count" => $count,
                "kit" => $kit,
                "map" => $map,
                "max" => $max
            ];

            TournamentTask::$players = [];
            TournamentTask::$squads = [];
            TournamentTask::$pvp = [];

            TournamentTask::$status = 0;
            TournamentTask::$time = $wait;

            Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le joueur §9" . $player->getName() . " §fa lancé un tournoi §9" . $name . " " . $count . "v" . $count . " §fvient d'être lancé ! Rejoignez le avec l'item tournoi au spawn !");
            Base::getInstance()->getServer()->getWorldManager()->loadWorld($map);

            Session::get($player)->setCooldown("tournament", 60 * 60 * 3);
        });
        $form->setTitle("Tournoi");
        $form->addLabel(Util::PREFIX . "Choisissez toutes les valeurs que vous voulez");
        $form->addInput(Util::PREFIX . "Nom du tournoi", "sumo");
        $form->addDropdown(Util::PREFIX . "Quel map", array_keys($config));
        $form->addDropdown(Util::PREFIX . "Quel kit", array_keys(Kit::getKits()));
        $form->addSlider(Util::PREFIX . "Nombre de potion(s)", 0, 40, -1, 0);
        $form->addSlider(Util::PREFIX . "Combien VS Combien?", 1, 15);
        $form->addSlider(Util::PREFIX . "Temps d'attente", 1, 60, -1, 30);
        $form->addSlider(Util::PREFIX . "Max de joueur", 2, 100, -1, 30);
        $player->sendForm($form);
    }

    protected function prepare(): void
    {
    }
}