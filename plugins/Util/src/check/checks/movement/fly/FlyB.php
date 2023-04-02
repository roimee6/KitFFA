<?php

namespace Util\check\checks\movement\fly;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use Util\check\Check;
use Util\session\Session;

class FlyB extends Check
{
    public function getMaxViolations(): int
    {
        return 9;
    }

    public function checkEvent(Session $session, Event $ev): void
    {
        if (!$ev instanceof PlayerMoveEvent) {
            return;
        }

        $player = $ev->getPlayer();

        if ($this->checkPlayer($player)) {
            return;
        }

        if (!$player->isUnderwater() && !$session->nearGround) {
            $offsetH = hypot($ev->getTo()->getX() - $ev->getFrom()->getX(), $ev->getTo()->getZ() - $ev->getFrom()->getZ());
            $offsetY = $ev->getTo()->getY() - $ev->getFrom()->getY();

            if ($offsetH > 0.0 && $offsetY == 0.0) {
                if (++$this->vl >= 10) {
                    $session->increaseVl($this);
                }
            } else {
                $this->vl = 0;
            }
        } else {
            $this->vl = 0;
        }
    }

    public function checkPacket(Session $session, DataPacket $pk): void
    {
    }
}