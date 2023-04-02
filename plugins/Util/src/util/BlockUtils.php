<?php

namespace Util\util;

use pocketmine\block\Block;
use pocketmine\player\Player;

class BlockUtils
{
    public static function getBlocksAround(Player $player): array
    {
        $bb = MathUtils::modifyBoundingBox($player->getBoundingBox(), 0, 0, 0.55, 0.6, 0, 0);

        $minX = floor($bb->minX);
        $minY = floor($bb->minY);
        $minZ = floor($bb->minZ);
        $maxX = floor($bb->maxX);
        $maxY = floor($bb->maxY);
        $maxZ = floor($bb->maxZ);

        $blocks = [];

        for ($z = $minZ; $z <= $maxZ; ++$z) {
            for ($x = $minX; $x <= $maxX; ++$x) {
                for ($y = $minY; $y <= $maxY; ++$y) {
                    $block = $player->getWorld()->getBlockAt($x, $y, $z);
                    $blocks[] = $block;
                }
            }
        }
        return $blocks;
    }

    public static function getUnderBlock(Player $player): Block
    {
        return $player->getWorld()->getBlock($player->getLocation()->subtract(0, 1, 1));
    }
}