<?php

namespace NCore\handler;

use BadFunctionCallException;
use NCore\Base;
use NCore\Session;
use OutOfBoundsException;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use function mb_strtolower;

class ScoreFactory
{
    private const OBJECTIVE_NAME = "objective";
    private const CRITERIA_NAME = "dummy";

    private const MIN_LINES = 1;
    private const MAX_LINES = 15;

    private static array $scoreboards = [];

    public static function updateScoreboard(Player $player): void
    {
        $session = Session::get($player);

        if (!$session->data["scoreboard"]) {
            return;
        }

        if (self::hasScore($player)) {
            self::setScore($player, "§9Nitro (§7" . strftime("%H:%M") . "§9)");

            $lines = [
                "§f ",
                "§l§9" . $player->getDisplayName(),
                "§fLigue: §9" . $session->getLeague(),
                "§fJoueurs: §9" . count(Base::getInstance()->getServer()->getOnlinePlayers()),
            ];

            if ($session->inCooldown("combat")) {
                $lines[] = "§9 ";
                $lines[] = "§l§9Combat";
                $lines[] = "§f" . $session->getCooldownData("combat")[1];
            }

            $lines[] = "§7 ";
            $lines[] = "     §7nitrofaction.fr    ";

            foreach ($lines as $key => $value) {
                self::setScoreLine($player, $key + 1, $value);
            }
        } else {
            self::setScore($player, "§9Nitro (§7" . strftime("%H:%M") . "§9)");
            self::updateScoreboard($player);
        }
    }

    public static function hasScore(Player $player): bool
    {
        return isset(self::$scoreboards[mb_strtolower($player->getXuid())]);
    }

    public static function setScore(Player $player, string $displayName, int $slotOrder = SetDisplayObjectivePacket::SORT_ORDER_ASCENDING, string $displaySlot = SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR, string $objectiveName = self::OBJECTIVE_NAME, string $criteriaName = self::CRITERIA_NAME): void
    {
        if (isset(self::$scoreboards[mb_strtolower($player->getXuid())])) {
            self::removeScore($player);
        }

        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = $displaySlot;

        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;

        $pk->criteriaName = $criteriaName;
        $pk->sortOrder = $slotOrder;

        $player->getNetworkSession()->sendDataPacket($pk);
        self::$scoreboards[mb_strtolower($player->getXuid())] = $objectiveName;
    }

    public static function removeScore(Player $player): void
    {
        $objectiveName = self::$scoreboards[mb_strtolower($player->getXuid())] ?? self::OBJECTIVE_NAME;
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $objectiveName;
        $player->getNetworkSession()->sendDataPacket($pk);
        unset(self::$scoreboards[mb_strtolower($player->getXuid())]);
    }

    public static function setScoreLine(Player $player, int $line, string $message, int $type = ScorePacketEntry::TYPE_FAKE_PLAYER): void
    {
        if (!isset(self::$scoreboards[mb_strtolower($player->getXuid())])) {
            throw new BadFunctionCallException("Cannot set a score to a player without a scoreboard");
        } else if ($line < self::MIN_LINES || $line > self::MAX_LINES) {
            throw new OutOfBoundsException("$line is out of range, expected value between " . self::MIN_LINES . " and " . self::MAX_LINES);
        }
        $entry = new ScorePacketEntry();
        $entry->objectiveName = self::$scoreboards[mb_strtolower($player->getXuid())] ?? self::OBJECTIVE_NAME;
        $entry->type = $type;
        $entry->customName = $message;
        $entry->score = $line;
        $entry->scoreboardId = $line;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->getNetworkSession()->sendDataPacket($pk);
    }
}