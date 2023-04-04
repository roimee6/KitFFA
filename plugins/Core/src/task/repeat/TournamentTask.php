<?php

namespace NCore\task\repeat;

use NCore\Base;
use NCore\handler\Cache;
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

    public static int $time = 30;
    public static int $status = 0;

    public static function run(): void
    {
        if (!self::$current) {
            return;
        }

        if (self::$status === 0) {
            if (self::$time === 0) {
                if ((self::$setting["count"] * 2) > count(self::$players) || count(self::$players) % self::$setting["count"] !== 0) {
                    self::$time += 10;
                    Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le tournoi ne comporte actuellement pas assez de joueur ou le nombre de joueur ne convient pas aux nombres de joueurs dans les equipes, §910 §fsecondes supplémentaires ajoutés au temps");
                    return;
                }

                self::$squads = self::makeGroups(self::$players, self::$setting["count"]);
                self::$players = [];

                self::nextSquad();
                self::run();

                return;
            }

            foreach (self::$players as $player) {
                if (self::goodPlayer($player)) {
                    $player->sendPopup(Util::PREFIX . "Début dans: §9" . self::$time);
                }
            }

            self::$time--;
        } else if (self::$status === 1) {
            foreach (self::$pvp as $id => $players) {
                $position = array_search($id, array_keys(self::$pvp));
                $data = explode(":", Cache::$config["tournaments"][self::$setting["map"]][strval($position)]);

                $world = Base::getInstance()->getServer()->getWorldManager()->getWorldByName(self::$setting["map"]);
                $position = new Position(intval($data[0]), intval($data[1]), intval($data[2]), $world);

                foreach ($players as $player) {
                    if (self::goodPlayer($player)) {
                        $player->teleport($position);
                        $player->setImmobile();

                        $player->sendPopup(Util::PREFIX . "Début du combat dans: §9" . self::$time);
                    }
                }
            }

            if (self::$time === 0) {
                foreach (self::$pvp as $players) {
                    foreach ($players as $player) {
                        if (self::goodPlayer($player)) {
                            $player->setImmobile(false);
                            Util::giveKit($player);

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
            var_dump("pvp");
        }
    }

    private static function nextSquad(): void
    {
        // CHECK SI YA BESOIN MEME ?

        self::$old = [];

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

        Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le combat entre les joueurs: §9" . $squad_one . " §fet §9" . $squad_two . " §fdébute dans §95 §fsecondes !");
    }

    public static function updatePlayer(Player $player): void
    {
        foreach (self::$pvp as $squad => $target) {
            if ($player === $target) {
                unset(self::$pvp[$squad][array_search($target, self::$pvp[$squad])]);
                // TODO FINIR ICI
            }
        }

        foreach (self::$pvp as $squad => $value) {
            if (count(self::$pvp[$squad]) === 0) {
                unset(self::$pvp[$squad]);

                $winner = array_keys(self::$pvp)[0];

                self::$squads[$winner] = self::$old[$winner];
                self::$pvp = [];

                self::nextSquad();
            }
        }
    }

    public static function goodPlayer(Player $player): bool
    {
        if ($player->isAlive() && $player->isConnected() && !$player->isCreative()) {
            return true;
        } else {
            return false;
        }
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
}