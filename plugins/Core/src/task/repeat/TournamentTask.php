<?php

namespace NCore\task\repeat;

use NCore\Base;
use NCore\handler\Cache;
use NCore\Session;
use NCore\Util;
use pocketmine\player\Player;
use pocketmine\world\Position;

class TournamentTask
{
    public static bool $current = false;
    public static array $setting = [];

    /* @var Player[] */
    public static array $players = [];
    public static array $squads = [];

    public static array $old = [];
    public static array $pvp = [];

    public static int $countProblemIssue = 0;
    public static int $countPlayers = 0;

    public static int $time = 30;
    public static int $status = 0;

    public static function run(): void
    {
        if (!self::$current) {
            return;
        }

        if (self::$status === 0) {
            if (self::$time === 0) {
                if (self::$setting["count"] * 2 > count(self::$players)) {
                    Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le tournoi ne comporte pas assez de joueurs pour débuter.. §930 §fsecondes d'attente ajouté");
                    self::addPlayerCountIssue();
                    return;
                } else if (count(self::$players) % self::$setting["count"] !== 0) {
                    Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le tournoi ne comporte pas assez de joueur pour créer toutes les équipes convenablement.. §930 §fsecondes d'attente ajouté");
                    self::addPlayerCountIssue();
                    return;
                }

                self::$countPlayers = count(self::$players);

                self::$squads = self::makeGroups(self::$players, self::$setting["count"]);
                self::$players = [];

                self::nextSquad();
                self::run();

                return;
            }

            foreach (self::$players as $player) {
                if (self::goodPlayer($player)) {
                    $player->sendTip(Util::PREFIX . "Début dans: §9" . self::$time);
                }
            }

            self::$time--;
        } else if (self::$status === 1) {
            foreach (self::$pvp as $id => $players) {
                $position = array_search($id, array_keys(self::$pvp));
                $data = explode(":", Cache::$config["tournaments"][self::$setting["map"]][strval($position)]);

                $world = Base::getInstance()->getServer()->getWorldManager()->getWorldByName(self::$setting["map"]);
                $position = new Position(floatval($data[0]), floatval($data[1]), floatval($data[2]), $world);

                foreach ($players as $player) {
                    if (self::goodPlayer($player)) {
                        Util::refresh($player);

                        $player->teleport($position);
                        $player->setImmobile();

                        $player->sendTip(Util::PREFIX . "Début du combat dans: §9" . self::$time);
                    }
                }
            }

            if (self::$time === 0) {
                foreach (self::$pvp as $players) {
                    foreach ($players as $player) {
                        if (self::goodPlayer($player)) {
                            $player->setImmobile(false);
                            Util::giveKit($player, self::$setting["kit"], self::$setting["potion"]);

                            $player->sendMessage(Util::PREFIX . "Le combat vient de commencer ! Bonne chance à toi");
                        }
                    }
                }

                self::$status = 2;
                self::run();

                return;
            }

            self::$time--;
        } else if (self::$status === 2) {
            foreach (self::$pvp as $players) {
                foreach ($players as $player) {
                    if (!self::goodPlayer($player)) {
                        self::updatePlayer($player);
                    }
                }
            }
        }
    }

    private static function addPlayerCountIssue(): void
    {
        if (self::$countProblemIssue === 3) {
            Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Dû à un problème lié au nombre de joueurs dans le tournoi le tournoi est annulé");
            self::stop();
            return;
        }

        self::$countProblemIssue++;
        self::$time += 30;
    }

    public static function stop(): void
    {
        self::$current = false;

        $worldMgr = Base::getInstance()->getServer()->getWorldManager();
        $world = $worldMgr->getWorldByName(self::$setting["map"]);

        $worldMgr->unloadWorld($world);
    }

    private static function makeGroups(array $players, int $number): array
    {
        shuffle($players);
        $result = array_chunk($players, $number);

        if (array_map("array_unique", $result) != $result) {
            return self::makeGroups($players, $number);
        }
        return $result;
    }

    private static function nextSquad(): void
    {
        self::$old = [];

        if (count(self::$squads) === 1) {
            self::stop();

            $players = self::$squads[array_key_first(self::$squads)];
            $format = self::format($players);

            $plurral = count($players) > 1 ? "s" : "";
            $get = count($players) > 1 ? "ont" : "a";

            foreach ($players as $player) {
                if ($player instanceof Player && $player->isConnected()) {
                    Session::get($player)->addValue("elo", self::$countPlayers * 2);
                    $player->sendMessage(Util::PREFIX . "Vous venez de gagner §9" . (self::$countPlayers * 2) . " §felo grace au tournoi remporté !");
                }
            }

            Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le" . $plurral . " joueur" . $plurral . " §9" . $format . " §f" . $get . " remporté" . $plurral . " le tournoi §9" . self::$setting["name"] . " §f!");
            return;
        }

        self::$status = 1;
        self::$time = 5;

        $rands = array_rand(self::$squads, 2);

        foreach ($rands as $rand) {
            self::$pvp[$rand] = self::$squads[$rand];
            self::$old[$rand] = self::$pvp[$rand];

            unset(self::$squads[$rand]);
        }

        $squads = array_values(self::$pvp);

        $squad_one = implode("§f, §9", array_map(fn($value) => $value->getName(), $squads[0]));
        $squad_two = implode("§f, §9", array_map(fn($value) => $value->getName(), $squads[1]));

        $name = (self::$setting["count"] === 1) ? "joueurs" : "équipes";
        $remaining = (self::$setting["count"] === 1) ? "restants" : "restantes";

        Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "§9" . count(self::$squads) + 2 . " §f" . $name . " " . $remaining . " dans le tournoi !");
        Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le combat entre les joueurs: §9" . $squad_one . " §fet §9" . $squad_two . " §fdébute dans §95 §fsecondes !");
    }

    public static function format(array $players): string
    {
        $format = implode(", ", array_map(fn($value) => $value->getName(), $players));

        if (substr_count($format, ",") > 0) {
            return preg_replace("~(.*)" . preg_quote(",", "~") . "~", "$1 et", $format, 1);
        } else {
            return $format;
        }
    }

    public static function goodPlayer(Player $player): bool
    {
        if (
            $player->isAlive() &&
            $player->isConnected() &&
            !$player->isCreative() &&
            !Session::get($player)->data["staff_mod"][0] &&
            $player->getWorld()->getDisplayName() === self::$setting["map"]
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function updatePlayer(Player $player): bool
    {
        $result = false;

        foreach (self::$pvp as $squad => $players) {
            foreach ($players as $target) {
                if ($player->getXuid() === $target->getXuid()) {
                    $result = true;
                    unset(self::$pvp[$squad][array_search($target, self::$pvp[$squad])]);
                }
            }
        }

        foreach (self::$pvp as $squad => $value) {
            if (count($value) === 0) {
                unset(self::$pvp[$squad]);
                $winner = array_key_first(self::$pvp);

                self::$squads[$winner] = self::$old[$winner];
                self::$pvp = [];

                foreach (self::$squads[$winner] as $player) {
                    if (self::goodPlayer($player)) {
                        Util::refresh($player, true);
                        Session::get($player)->removeCooldown("combat");
                    }
                }

                self::nextSquad();
            }
        }
        return $result;
    }

    public static function getPlayers(): array
    {
        $result = [];

        foreach (self::$pvp as $value) {
            foreach ($value as $player) {
                if (self::goodPlayer($player)) {
                    $result[] = $player->getName();
                }
            }
        }
        return $result;
    }
}