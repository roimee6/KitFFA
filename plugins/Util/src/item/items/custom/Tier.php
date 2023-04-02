<?php

namespace Util\item\items\custom;

use Util\util\IdsUtils;

class Tier
{
    public static function applyDamage(int $amount, Armor|Durable $item): bool
    {
        if ($item->isUnbreakable() || $item->isBroken()) {
            return false;
        }

        $baseDurability = $item->getMaxDurability();
        $newDurability = self::getMaxDurabilityFromId($item->getId());

        if ($item->getDamage() > 1 && $item->customDamage === 0) {
            $item->customDamage = floor(($item->getDamage() / $baseDurability) * $newDurability);
        }

        $item->customDamage += $amount;
        $item->setDamage(ceil(($baseDurability / $newDurability) * $item->customDamage));

        return true;
    }

    public static function getMaxDurabilityFromId(int $id): int
    {
        return round([
            IdsUtils::IRIS_SWORD => 3048,
            IdsUtils::RAINBOW_SWORD => 3048,

            IdsUtils::IRIS_HELMET => 701 * 2,
            IdsUtils::IRIS_CHESTPLATE => 1021 * 2,
            IdsUtils::IRIS_LEGGINGS => 956 * 2,
            IdsUtils::IRIS_BOOTS => 829 * 2,

            IdsUtils::RAINBOW_HELMET => 701 * 2,
            IdsUtils::RAINBOW_CHESTPLATE => 1021 * 2,
            IdsUtils::RAINBOW_LEGGINGS => 956 * 2,
            IdsUtils::RAINBOW_BOOTS => 829 * 2,
        ][$id]);
    }
}