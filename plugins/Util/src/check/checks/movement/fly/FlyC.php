<?php

namespace Util\check\checks\movement\fly;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use Util\check\Check;
use Util\session\Session;

class FlyC extends Check
{
    public function getMaxViolations(): int
    {
        return 11;
    }

    public function checkEvent(Session $session, Event $ev): void
    {
        if (!$ev instanceof PlayerMoveEvent) {
            return;
        }

        $player = $ev->getPlayer();

        if ($this->checkPlayer($player, $session)) {
            return;
        }

        $serverAirTicks = $player->getInAirTicks();
        $deltaY = $session->deltaY;
        $lastDeltaY = $session->lastDeltaY;
        $acceleration = $deltaY - $lastDeltaY;

        $exempt = ($session->nearGround || $player->isUnderwater() || $session->liquidTicks < 8);
        $invalid = !$session->inVoid() && $acceleration > 0.0 && $serverAirTicks > 8;

        if ($invalid && !$exempt) {
            if (++$this->vl > 2) {
                $session->increaseVl($this);
            }
        } else {
            $this->vl -= $this->vl > 0 ? 0.1 : 0;
        }
    }

    public function checkPacket(Session $session, DataPacket $pk): void
    {
    }
}