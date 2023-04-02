<?php

namespace Util\item;

use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\PotionType;
use pocketmine\item\StringToItemParser;
use pocketmine\item\ToolTier;
use ReflectionClass;
use Util\item\items\custom\Armor;
use Util\item\items\custom\Sword;
use Util\item\items\PotionLauncher;
use Util\item\items\SplashPotion as ItemSplashPotion;
use Util\util\IdsUtils;

class ItemManager
{
    public static function startup(): void
    {
        self::createToolTier("iris", 33);
        self::createToolTier("rainbow", 60);

        foreach (PotionType::getAll() as $type) {
            $typeId = PotionTypeIdMap::getInstance()->toId($type);
            self::registerItem(new ItemSplashPotion(new ItemIdentifier(ItemIds::SPLASH_POTION, $typeId), $type->getDisplayName() . " Splash Potion", $type), false);
        }

        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::IRIS_HELMET, 0), "Iris Helmet", new ArmorTypeInfo(3, 78, ArmorInventory::SLOT_HEAD)));
        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::IRIS_CHESTPLATE, 0), "Iris Chestplate", new ArmorTypeInfo(8, 113, ArmorInventory::SLOT_CHEST)));
        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::IRIS_LEGGINGS, 0), "Iris Leggings", new ArmorTypeInfo(6, 106, ArmorInventory::SLOT_LEGS)));
        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::IRIS_BOOTS, 0), "Iris Boots", new ArmorTypeInfo(4, 92, ArmorInventory::SLOT_FEET)));

        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::RAINBOW_HELMET, 0), "Rainbow Helmet", new ArmorTypeInfo(3, 56, ArmorInventory::SLOT_HEAD)));
        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::RAINBOW_CHESTPLATE, 0), "Rainbow Chestplate", new ArmorTypeInfo(8, 81, ArmorInventory::SLOT_CHEST)));
        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::RAINBOW_LEGGINGS, 0), "Rainbow Leggings", new ArmorTypeInfo(6, 76, ArmorInventory::SLOT_LEGS)));
        self::registerItem(new Armor(new ItemIdentifier(IdsUtils::RAINBOW_BOOTS, 0), "Rainbow Boots", new ArmorTypeInfo(4, 66, ArmorInventory::SLOT_FEET)));

        self::registerItem(new Sword(new ItemIdentifier(IdsUtils::IRIS_SWORD, 0), "Iris Sword", ToolTier::IRIS()));
        self::registerItem(new Sword(new ItemIdentifier(IdsUtils::RAINBOW_SWORD, 0), "Rainbow Sword", ToolTier::IRIS()));

        self::registerItem(new PotionLauncher(new ItemIdentifier(ItemIds::NAUTILUS_SHELL, 0), "Potion Launcher"));
    }

    private static function createToolTier(string $name, int $durability): void
    {
        $ref = new ReflectionClass(ToolTier::class);
        $register = $ref->getMethod("register");

        $register->setAccessible(true);
        $constructor = $ref->getConstructor();

        $constructor->setAccessible(true);
        $instance = $ref->newInstanceWithoutConstructor();

        $constructor->invoke($instance, $name, 6, $durability, 8, 8);
        $register->invoke(null, $instance);
    }

    private static function registerItem(Item $item, bool $registerToParser = true): void
    {
        ItemFactory::getInstance()->register($item, true);

        CreativeInventory::getInstance()->remove($item);
        CreativeInventory::getInstance()->add($item);

        if ($registerToParser) {
            $name = strtolower($item->getName());
            $name = str_replace(" ", "_", $name);
            StringToItemParser::getInstance()->register($name, fn() => $item);
        }
    }
}