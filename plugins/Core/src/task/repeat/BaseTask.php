<?php

namespace NCore\task\repeat;

use NCore\Base;
use NCore\handler\Cache;
use NCore\handler\ScoreFactory;
use NCore\Session;
use NCore\Util;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;
use Util\item\items\custom\Armor;
use Util\util\IdsUtils;

class BaseTask extends Task
{
    public static array $combat = [];
    public static array $enderpearl = [];

    private array $kits = [];
    private int $tick = 1;

    public function onRun(): void
    {
        $this->tick++;
        $players = Base::getInstance()->getServer()->getOnlinePlayers();

        if ($this->tick % 20000 == 0) {
            Cache::save();
        }

        if ($this->tick % 20 == 0) {
            TournamentTask::run();
        }

        foreach ($players as $player) {
            if (!$player->isAlive()) {
                continue;
            }

            if ($player->getPosition()->getY() < 0) {
                $player->kill();
            }

            $session = Session::get($player);

            $player->setScoreTag("§7" . round($player->getHealth(), 2) . " §c❤");
            $player->getHungerManager()->setFood(20);

            if (in_array($player->getName(), self::$combat)) {
                if (!$session->inCooldown("combat")) {
                    $player->sendMessage(Util::PREFIX . "Vous n'êtes désormais plus en combat");
                    unset(self::$combat[array_search($player->getName(), self::$combat)]);
                }
            }

            if (in_array($player->getName(), self::$enderpearl)) {
                if ($session->inCooldown("enderpearl", true)) {
                    $data = $session->getCooldownData("enderpearl");
                    $cooldown = $data[0];

                    $progress = $cooldown - microtime(true);
                    $player->getXpManager()->setXpAndProgress(intval($cooldown - time()), max(0, $progress / 15));
                } else {
                    $player->sendPopup(Util::PREFIX . "Vous n'êtes plus en cooldown perle");
                    $player->getXpManager()->setXpAndProgress(0, 0);

                    unset(self::$enderpearl[array_search($player->getName(), self::$enderpearl)]);
                }
            }

            if ($this->tick % 10 == 0) {
                foreach ($player->getArmorInventory()->getContents() as $key => $item) {
                    if (
                        $item instanceof Armor &&
                        in_array($item->getId(), [IdsUtils::RAINBOW_HELMET, IdsUtils::RAINBOW_CHESTPLATE, IdsUtils::RAINBOW_LEGGINGS, IdsUtils::RAINBOW_BOOTS])
                    ) {
                        $item = $item->setCustomColor(new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
                        $player->getArmorInventory()->setItem($key, $item);
                    }
                }
            }

            if ($this->tick % 20 == 0) {
                $session->data["ping"][] = $player->getNetworkSession()->getPing();

                if ($session->data["ping"] > 5) {
                    array_shift($session->data["ping"]);
                }

                ScoreFactory::updateScoreboard($player);
            }

            if ($player->getGamemode() === GameMode::SURVIVAL() && !$session->data["staff_mod"][0]) {
                $kit = $this->kits[$player->getName()] ?? false;

                if (Util::insideZone($player->getPosition(), "spawn")) {
                    if ($session->inCooldown("combat")) {
                        $distance = -1;
                        $vector = new Vector3(0, 0, 0);

                        foreach (Cache::$config["points"] as $xz => $motion) {
                            list ($x, $z) = explode(":", $xz);

                            $vect = new Vector3($x, 0, $z);
                            $dist = $player->getPosition()->distance($vect);

                            if (0 > $distance || $distance > $dist) {
                                $distance = $dist;

                                list($x, $z) = explode(":", $motion);
                                $vector = new Vector3($x, 0.6, $z);
                            }
                        }

                        $player->setMotion($vector);
                        continue;
                    }

                    if ($kit !== "spawn") {
                        Util::refresh($player);
                        Util::giveItems($player);

                        $this->kits[$player->getName()] = "spawn";
                    }
                } else {
                    if ($kit !== "pvp") {
                        Util::refresh($player);
                        Util::giveKit($player);

                        $this->kits[$player->getName()] = "pvp";
                    }
                }
            }
        }
    }
}