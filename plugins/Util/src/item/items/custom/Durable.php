<?php

namespace Util\item\items\custom;

use pocketmine\item\Durable as PmDurable;
use pocketmine\item\Item;

abstract class Durable extends PmDurable
{
    public int $customDamage = 0;

    abstract public function getMaxDurability(): int;

    public function setDamage(int $damage): Item
    {
        if ($damage === 0) {
            $this->customDamage = 0;
        }
        return parent::setDamage($damage);
    }

    public function applyDamage(int $amount): bool
    {
        $durable = Tier::applyDamage($amount, $this);

        if (!$durable) {
            return false;
        } else if ($this->isBroken()) {
            $this->onBroken();
        }
        return true;
    }
}