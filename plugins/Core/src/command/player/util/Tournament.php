<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use jojoe77777\FormAPI\CustomForm;
use NCore\Base;
use NCore\handler\Cache;
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
                if (!$sender->hasPermission("staff.group")) {
                    $sender->sendMessage(Util::PREFIX . "Aucun tournoi en cours ! Demandez à un joueur gradé d'en lancer un !");
                    return;
                }

                $this->createTournamentForm($sender);
            } else {
                if (Session::get($sender)->inCooldown("combat")) {
                    $sender->sendMessage(Util::PREFIX . "Vous ne pouvez pas rejoindre de tournoi en combat");
                    return;
                } else if (count(TournamentTask::$players) >= TournamentTask::$setting["max"]) {
                    $sender->sendMessage(Util::PREFIX . "L'hôte de l'event a limité le nombre de joueurs maximum à §9" . TournamentTask::$setting["max"] . "§f, vous ne pourrez donc pas pvp..");
                }

                $setting = TournamentTask::$setting;
                $config = Cache::$config["tournaments"][$setting["map"]];

                if (TournamentTask::$status === 0) {
                    $already = false;

                    foreach (TournamentTask::$players as $key => $player) {
                        if ($player->getXuid() === $sender->getXuid()) {
                            unset(TournamentTask::$players[$key]);
                            $already = true;
                        }
                    }

                    TournamentTask::$players[] = $sender;
                    $message = Util::PREFIX . "Le joueur §9" . $sender->getName() . " §fvient de rejoindre le tournoi §9" . $setting["map"] . " §f(§9" . count(TournamentTask::$players) . "§f/§9" . $setting["max"] . "§f)";

                    if ($already) {
                        $sender->sendMessage($message);
                    } else {
                        Base::getInstance()->getServer()->broadcastMessage($message);
                    }
                }

                $world = Base::getInstance()->getServer()->getWorldManager()->getWorldByName($setting["map"]);
                $data = explode(":", $config["spectate"]);

                $position = new Position(intval($data[0]), intval($data[1]), intval($data[2]), $world);
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

            $event = array_keys($config)[$data[1]];
            $kit = array_keys(Kit::getKits())[$data[2]];

            $count = $data[3];
            $wait = $data[4];
            $max = $data[5];

            TournamentTask::$current = true;

            TournamentTask::$setting = [
                "count" => $count,
                "kit" => $kit,
                "map" => $event,
                "max" => $max
            ];

            TournamentTask::$players = [];
            TournamentTask::$squads = [];
            TournamentTask::$pvp = [];

            TournamentTask::$status = 0;
            TournamentTask::$time = $wait;

            Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Un tournoi §9" . $event . " " . $count . "v" . $count . " §fvient d'être lancé ! Rejoignez le avec l'item tournoi au spawn !");
            Base::getInstance()->getServer()->getWorldManager()->loadWorld($event);
        });
        $form->setTitle("Tournoi");
        $form->addLabel(Util::PREFIX . "Choisissez toutes les valeurs que vous voulez");
        $form->addDropdown(Util::PREFIX . "Quel map", array_keys($config));
        $form->addDropdown(Util::PREFIX . "Quel kit", array_keys(Kit::getKits()));
        $form->addSlider(Util::PREFIX . "Combien VS Combien?", 1, 15);
        $form->addSlider(Util::PREFIX . "Temps d'attente", 1, 60, -1, 30);
        $form->addSlider(Util::PREFIX . "Max de joueur", 2, 100, -1, 30);
        $player->sendForm($form);
    }

    protected function prepare(): void
    {
    }
}