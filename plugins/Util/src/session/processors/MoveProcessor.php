<?php

namespace Util\session\processors;

use pocketmine\block\Ice;
use pocketmine\block\Ladder;
use pocketmine\block\Liquid;
use pocketmine\block\Vine;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerMoveEvent;
use Util\session\Session;
use Util\util\BlockUtils;

class MoveProcessor extends Processor
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    public function process(Event $ev)
    {
        if (!$ev instanceof PlayerMoveEvent) {
            return;
        }

        $player = $ev->getPlayer();

        if (!$player->isOnline()) {
            return;
        }

        $session = $this->getSession();

        $session->nearGround = (
            $player->isOnGround()
            || $player->getWorld()->getBlock($player->getPosition()->subtract(0, 1, 0))->isSolid()
            || $player->getWorld()->getBlock($player->getPosition()->subtract(0, 1.5, 0))->isSolid()
        );

        $session->lastDeltaY = $session->deltaY;
        $session->deltaY = $ev->getTo()->getY() - $ev->getFrom()->getY();

        if ($player->isOnGround()) {
            $session->groundTicks++;
        } else {
            $session->groundTicks = 0;
        }

        $liquids = 0;
        $climbs = 0;
        $ices = 0;

        foreach (BlockUtils::getBlocksAround($player) as $block) {
            if ($block instanceof Liquid) {
                $liquids++;
            }

            if ($block instanceof Ladder || $block instanceof Vine) {
                $climbs++;
            }

            if ($block instanceof Ice) {
                $ices++;
            }
        }

        if ($liquids > 0) {
            ++$session->liquidTicks;
        } else {
            $session->liquidTicks = 0;
        }

        if ($climbs > 0) {
            ++$session->climbableTicks;
        } else {
            $session->climbableTicks = 0;
        }

        if ($ices > 0) {
            ++$session->iceTicks;
        } else {
            $session->iceTicks = 0;
        }
    }
}