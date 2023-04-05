<?php

namespace Util\check\checks\combat\autoclicker;

use NCore\command\staff\tool\CpsViewer;
use NCore\Session as CoreSession;
use NCore\Util;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\player\Player;
use Util\check\Check;
use Util\session\Session;

class AutoClickerA extends Check
{
    public function getMaxViolations(): int
    {
        return 16;
    }

    public function checkEvent(Session $session, Event $ev): void
    {
    }

    public function checkPacket(Session $session, DataPacket $pk): void
    {
        if (
            ($pk instanceof InventoryTransactionPacket && $pk->trData instanceof UseItemOnEntityTransactionData) ||
            ($pk instanceof LevelSoundEventPacket && $pk->sound === LevelSoundEvent::ATTACK_NODAMAGE)
        ) {
            $this->check($session, $pk instanceof InventoryTransactionPacket);
        }
    }

    private function check(Session $session, bool $hit): void
    {
        $player = $session->getPlayer();

        $time = microtime(true);
        array_unshift($session->clicks, $time);

        $cps = count(array_filter($session->clicks, static function (float $t) use ($time): bool {
            return ($time - $t) <= 1;
        }));

        $playerSession = CoreSession::get($player);

        if (($playerSession->data["cps"] ?? false) || ($hit && ($playerSession->data["combo"] ?? false))) {
            $playerSession->sendTip($cps);
        }

        if (($name = array_search($player->getName(), CpsViewer::$cpsViewer)) !== false) {
            $target = Util::getPlayer($name);

            if (!$target instanceof Player) {
                unset(CpsViewer::$cpsViewer[$name]);
            } else {
                $target->sendPopup(Util::PREFIX . "Cps de §9" . $player->getName() . " §f» §9" . $cps);
            }
        }

        if ($cps > 30 && !$this->checkPing($player)) {
            if (++$this->vl > 6) {
                $session->increaseVl($this);
            }
        } else {
            $this->vl -= $this->vl > 0 ? 0.25 : 0;
        }
    }
}