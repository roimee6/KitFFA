<?php

namespace Util\entity;

use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use Util\entity\SplashPotion as EntitySplashPotion;

class EntityManager
{
    public static function startup(): void
    {
        EntityFactory::getInstance()->register(DeadEntity::class, function (World $world, CompoundTag $nbt): DeadEntity {
            return new DeadEntity(EntityDataHelper::parseLocation($nbt, $world), DeadEntity::parseSkinNBT($nbt), $nbt);
        }, ["DeadEntity"]);

        EntityFactory::getInstance()->register(EntitySplashPotion::class, function (World $world, CompoundTag $nbt): EntitySplashPotion {
            $potionType = PotionTypeIdMap::getInstance()->fromId($nbt->getShort("PotionId", PotionTypeIds::WATER));
            if ($potionType === null) throw new SavedDataLoadingException();
            return new EntitySplashPotion(EntityDataHelper::parseLocation($nbt, $world), null, $potionType, $nbt);
        }, ["ThrownPotion", "minecraft:potion", "thrownpotion"], EntityLegacyIds::SPLASH_POTION);
    }
}