<?php

namespace Util\item;

use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\item\ItemFactory;
use ReflectionClass;
use ReflectionProperty;
use Util\Base;

class CraftManager
{
    public static function startup(): void
    {
        $craftMgr = Base::getInstance()->getServer()->getCraftingManager();
        $reflectionClass = new ReflectionClass($craftMgr);

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            if ($property->getName() === "craftingRecipeIndex") {
                $property->setAccessible(true);
                $property->setValue($craftMgr, []);
                $property->setAccessible(false);
            }
        }
    }
}