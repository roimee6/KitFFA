<?php

namespace Util\check\checks\movement\fly;

use NCore\Session as CoreSession;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Ladder;
use pocketmine\block\Water;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\player\Player;
use Util\check\Check;
use Util\session\Session;
use Util\util\BlockUtils;

class FlyA extends Check
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

        if (BlockUtils::getUnderBlock($player)->getId() == BlockLegacyIds::AIR && !$player->isOnGround()) {
            if (!is_null($session->lastLocation) && !$session->lastLocation->equals($player->getLocation())) {
                if ($player->getLocation()->getY() == $session->lastLocation->getY() && $player->getInAirTicks() >= 120) {
                    ++$session->blocksInAir;
                } else {
                    $session->blocksInAir = 0;
                }
            }

            $session->lastLocation = $player->getLocation();
        } else {
            $session->blocksInAir = 0;
        }
    }

    public function checkPacket(Session $session, DataPacket $pk): void
    {
    }
}