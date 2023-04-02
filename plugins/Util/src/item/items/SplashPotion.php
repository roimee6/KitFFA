<?php

namespace Util\item\items;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\PotionType;
use pocketmine\item\SplashPotion as PmSplashPotion;
use pocketmine\player\Player;
use Util\entity\SplashPotion as EntitySplashPotion;

class SplashPotion extends PmSplashPotion
{
    private PotionType $potionType;

    public function __construct(ItemIdentifier $identifier, string $name, PotionType $potionType)
    {
        parent::__construct($identifier, $name, $potionType);
        $this->potionType = $potionType;
    }

    public function getThrowForce(): float
    {
        return 0.5;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new EntitySplashPotion($location, $thrower, $this->potionType);
    }
}