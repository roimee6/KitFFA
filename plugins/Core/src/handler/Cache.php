<?php

namespace NCore\handler;

use NCore\Base;
use NCore\Util;

class Cache
{
    public static array $players;

    public static array $config;
    public static array $bans;

    public static function startup(): void
    {
        @mkdir(Base::getInstance()->getDataFolder() . "data/");
        @mkdir(Base::getInstance()->getDataFolder() . "data/players");

        Base::getInstance()->saveResource("config.yml", true);

        Cache::$config = Base::getInstance()->getConfig()->getAll();
        Cache::$bans = Util::getFile("bans")->getAll();

        foreach (Util::listAllFiles(Base::getInstance()->getDataFolder() . "data/players") as $file) {
            $path = pathinfo($file);
            $username = $path["filename"];

            $file = Util::getFile("players/" . $username);

            Cache::$players["kill"][$username] = $file->get("kill");
            Cache::$players["death"][$username] = $file->get("death");
            Cache::$players["elo"][$username] = $file->get("elo");
            Cache::$players["killstreak"][$username] = $file->get("killstreak");
            Cache::$players["upper_name"][$username] = $file->get("upper_name");

            foreach (Cache::$config["saves"] as $column) {
                Cache::$players[$column][$username] = $file->get($column, []);
            }
        }
    }

    public static function save(): void
    {
        $file = Util::getFile("bans");

        $file->setAll(self::$bans);
        $file->save();
    }
}