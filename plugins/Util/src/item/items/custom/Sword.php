<?php

namespace Util\item\items\custom;

use NCore\Util;
use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ToolTier;
use Util\util\IdsUtils;

class Sword extends Durable
{
    protected ToolTier $tier;

    public function __construct(ItemIdentifier $identifier, string $name, ToolTier $tier)
    {
        parent::__construct($identifier, $name);
        $this->tier = $tier;
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }

    /** @noinspection PhpUnused */

    public function getMaxDurability(): int
    {
        return $this->tier->getMaxDurability();
    }

    public function getFuelTime(): int
    {
        if ($this->tier->equals(ToolTier::WOOD())) {
            return 200;
        }
        return 0;
    }

    public function getBlockToolType(): int
    {
        return BlockToolType::SWORD;
    }

    public function getAttackPoints(): int
    {
        return $this->tier->getBaseAttackPoints();
    }

    public function getBlockToolHarvestLevel(): int
    {
        return 1;
    }

    public function getMiningEfficiency(bool $isCorrectTool): float
    {
        $efficiency = 1;
        if ($isCorrectTool) {
            $efficiency = $this->getBaseMiningEfficiency();
            if (($enchantmentLevel = $this->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY())) > 0) {
                $efficiency += ($enchantmentLevel ** 2 + 1);
            }
        }

        return $efficiency * 1.5; //swords break any block 1.5x faster than hand
    }

    protected function getBaseMiningEfficiency(): float
    {
        return 10;
    }

    public function onDestroyBlock(Block $block): bool
    {
        if (!$block->getBreakInfo()->breaksInstantly()) {
            return $this->applyDamage(2);
        }
        return false;
    }

    public function onAttackEntity(Entity $victim): bool
    {
        if ($this->getId() === IdsUtils::IRIS_SWORD) {
            if (mt_rand(1, 200) === 1) {
                Util::makeLightning($victim);
            }
        }

        return $this->applyDamage(1);
    }
}