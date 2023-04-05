<?php

namespace Util\check\checks\movement\airjump;

use pocketmine\event\Event;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use Util\check\Check;
use Util\session\Session;

class AirJumpA extends Check
{
    public function getMaxViolations(): int
    {
        return 4;
    }

    public function checkEvent(Session $session, Event $ev): void
    {
        if (!$ev instanceof PlayerJumpEvent) {
            return;
        }

        $player = $ev->getPlayer();

        if ($this->checkPlayer($player, $session)) {
            return;
        }

        $exempt = ($session->nearGround || $session->inVoid() || $player->getInAirTicks() < 4 || $player->noDamageTicks > 4);
        $invalid = !$player->isOnGround();

        if ($invalid && !$exempt) {
            $session->increaseVl($this);
        }
    }

    public function checkPacket(Session $session, DataPacket $pk): void
    {
    }
}