<?php

namespace Util\check\checks\movement\fly;

use NCore\Session as CoreSession;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use Util\check\Check;
use Util\session\Session;

class FlyD extends Check
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

        if ($this->checkPlayer($player, $session)) {
            return;
        }

        $deltaY = $session->deltaY;
        $lastDeltaY = $session->lastDeltaY;
        $difference = abs($deltaY - $lastDeltaY);

        $exempt = ($session->inVoid() || $player->getInAirTicks() < 8 || $player->isUnderwater() && $session->liquidTicks < 8) || abs($deltaY) > 3.0 || abs($lastDeltaY) > 3.0;
        $invalid = $difference < 0.01 && !$session->nearGround;

        if ($invalid && !$exempt) {
            if (++$this->vl > 4) {
                $session->increaseVl($this);
            }
        } else {
            $this->vl -= $this->vl > 0 ? 0.25 : 0;
        }
    }

    public function checkPacket(Session $session, DataPacket $pk): void
    {
    }
}