<?php

namespace NCore;

use NCore\handler\Cache;
use NCore\task\BaseTask;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use WeakMap;

class Session
{
    /** @phpstan-var WeakMap<Player, Session> */
    private static WeakMap $sessions;

    public function __construct(private Player $player, public array $data)
    {
    }

    public static function get(Player $player): Session
    {
        self::$sessions ??= new WeakMap();
        return self::$sessions[$player] ??= self::loadSessionData($player);
    }

    /** @noinspection PhpUnused */
    public function sendTip(int $cps): void
    {
        $data = [];

        if ($this->data["cps"] ?? false) {
            $data[] = "§9CPS: §f" . $cps;
        }

        if ($this->data["combo"] ?? false) {
            $data[] = "§9Combo: §f" . $this->data["combo_count"];
        }

        if (count($data) !== 0) {
            $this->player->sendTip(Util::PREFIX . implode(" §f| §r", $data) . " §l§9«");
        }
    }

    private static function loadSessionData(Player $player): Session
    {
        $username = strtolower($player->getName());

        $file = Util::getFile("players/" . $username);
        $data = $file->getAll();

        if ($data === []) {
            $data = Cache::$config["default_data"];
        }

        $data["reply"] = null;
        $data["combo_count"] = 0;
        $data["ping"] = [];
        $data["upper_name"] = $player->getName();

        $ip = $player->getNetworkSession()->getIp();
        $uuid = $player->getNetworkSession()->getPlayerInfo()->getUuid()->toString();
        $did = $player->getPlayerInfo()->getExtraData()["DeviceId"];
        $ssi = $player->getPlayerInfo()->getExtraData()["SelfSignedId"];
        $cid = $player->getPlayerInfo()->getExtraData()["ClientRandomId"];

        if (!in_array($ip, $data["ip"])) $data["ip"][] = $ip;
        if (!in_array($uuid, $data["uuid"])) $data["uuid"][] = $uuid;
        if (!in_array($did, $data["did"])) $data["did"][] = $did;
        if (!in_array($ssi, $data["ssi"])) $data["ssi"][] = $ssi;
        if (!in_array($cid, $data["cid"])) $data["cid"][] = $cid;

        foreach ($data as $key => $value) {
            if (isset(Cache::$players[$key])) {
                Cache::$players[$key][$username] = $value;
            }
        }

        return new Session(
            $player,
            $data
        );
    }

    public function saveSessionData(): void
    {
        $player = $this->player;
        $file = Util::getFile("players/" . strtolower($player->getName()));

        $file->setAll($this->data);
        $file->save();
    }

    public function removeCooldown(string $key): void
    {
        unset($this->data["cooldown"][$key]);
    }

    public function setCooldown(string $key, int $time, array $value = [], bool $precise = false): void
    {
        if ($key === "combat" && $this->player->getGamemode() === GameMode::CREATIVE()) {
            return;
        } else if ($key === "combat" && !self::inCooldown("combat")) {
            $this->player->sendMessage(Util::PREFIX . "Vous êtes désormais en combat, vous ne pouvez plus aller au spawn !");
            BaseTask::$combat[] = $this->player->getName();
        }

        $t = $precise ? microtime(true) : time();
        $this->data["cooldown"][$key] = array_merge([$t + $time], $value);
    }

    public function inCooldown(string $key, bool $precise = false): bool
    {
        if ($key === "combat" && $this->player->getGamemode() === GameMode::CREATIVE()) {
            return false;
        } else {
            $time = $precise ? microtime(true) : time();
            return isset($this->data["cooldown"][$key]) && $this->data["cooldown"][$key][0] > $time;
        }
    }

    public function getLeague(): string
    {
        $percentage = $this->getPercentage();

        foreach (Cache::$config["leagues"] as $prct => $league) {
            if (floatval($prct) >= $percentage) {
                return $league;
            }
        }
        return Cache::$config["leagues"]["101"];
    }

    public function getPercentage(): float
    {
        $leaderboard = Cache::$players["elo"] ?? [];
        $count = count($leaderboard);

        arsort($leaderboard);

        $leaderboard = array_keys($leaderboard);
        $name = strtolower($this->player->getName());

        $position = intval(array_search($name, $leaderboard));
        return round(($position / ($count + 1)) * 100, 2);
    }

    public function addValue(string $key, int $value, bool $substraction = false): void
    {
        $this->data[$key] = ($substraction ? $this->data[$key] - $value : $this->data[$key] + $value);

        if (isset(Cache::$players[$key])) {
            $username = strtolower($this->player->getName());
            $actual = Cache::$players[$key][$username] ?? $this->data[$key];

            Cache::$players[$key][$username] = ($substraction ? $actual - $value : $actual + $value);
        }
    }

    public function setValue(string $key, int $value): void
    {
        $this->data[$key] = $value;

        if (isset(Cache::$players[$key])) {
            Cache::$players[$key][strtolower($this->player->getName())] = $value;
        }
    }

    public function getCooldownData(string $key): array
    {
        return $this->data["cooldown"][$key] ?? [time(), null, null, null];
    }
}