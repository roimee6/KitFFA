<?php

namespace NCore\handler;

use NCore\Base;
use NCore\Session;
use NCore\Util;
use pocketmine\player\Player;

class RankAPI
{
    public static function existRank(string $rank): bool
    {
        return isset(Cache::$config["ranks"][$rank]);
    }

    public static function hasRank(Player $player, string $rank): bool
    {
        $_rank = self::getRank($player->getName());

        if ($rank === $_rank) {
            return true;
        }

        if (!self::isStaff($_rank)) {
            $passed = false;

            foreach (array_keys(Cache::$config["ranks"]) as $value) {
                if (!$passed && $value === $_rank) {
                    return false;
                } else if ($rank === $value) {
                    $passed = true;
                }
            }
        }
        return true;
    }

    public static function getRank(string $name): ?string
    {
        $name = strtolower($name);
        $player = Util::getPlayer($name);

        if ($player instanceof Player) {
            $session = Session::get($player);
            $rank = $session->data["rank"];
        } else {
            $file = Util::getFile("players/" . $name);
            $rank = $file->get("rank", "joueur");
        }
        return $rank;
    }

    public static function isStaff(?string $rank): bool
    {
        return in_array($rank, ["guide", "moderateur", "sm", "administrateur", "fondateur"]);
    }

    public static function setRank(string $name, string $rank, bool $save = true): void
    {
        $name = strtolower($name);
        $player = Util::getPlayer($name);

        if ($player instanceof Player) {
            $session = Session::get($player);
            $session->data["rank"] = $rank;

            self::updateNameTag($player);
            self::addPermissions($player);

            if ($save) {
                self::saveRank($player->getXuid(), $rank);
            }
        } else {
            $file = Util::getFile("players/" . $name);

            if ($file->getAll() !== []) {
                $file->set("rank", $rank);
                $file->save();

                if ($save) {
                    self::saveRank($file->get("xuid"), $rank);
                }
            }
        }
    }

    public static function updateNameTag(Player $player): void
    {
        $name = $player->getName();
        $rank = ($name === $player->getDisplayName()) ? self::getRank($name) : "joueur";

        $prefix = self::getRankValue($rank, "gamertag");
        $replace = self::setReplace($prefix, $player);

        $player->setNameTag($replace);
        $player->setNameTagAlwaysVisible();
    }

    public static function getRankValue(string $rank, string $value): mixed
    {
        return Cache::$config["ranks"][$rank][$value];
    }

    public static function setReplace(string $replace, Player $player, string $msg = ""): string
    {
        $session = Session::get($player);

        return str_replace(
            ["{name}", "{league}", "{msg}"],
            [$player->getDisplayName(), $session->getLeague(), $msg],
            $replace
        );
    }

    public static function addPermissions(Player $player): void
    {
        $session = Session::get($player);

        if (RankAPI::isStaff($session->data["rank"]) || $player->hasPermission("pocketmine.group.operator")) {
            $player->addAttachment(Base::getInstance(), "staff.group", true);
            $player->addAttachment(Base::getInstance(), "pocketmine.command.teleport", true);
        }
    }

    public static function saveRank(string $value, string $key): void
    {
        $file = Util::getFile("ownings");
        $data = $file->get($value) ?? [];

        $data["rank"] = $key;

        $file->set($value, $data);
        $file->save();
    }
}