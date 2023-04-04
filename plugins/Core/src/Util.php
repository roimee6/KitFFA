<?php

namespace NCore;

use NCore\handler\Cache;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\Position;
use Util\item\items\custom\Armor;
use Util\util\IdsUtils;
use Webmozart\PathUtil\Path;

class Util
{
    const PREFIX = "§9§l» §r§f";

    public static function getPlayer(string $name): ?Player
    {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;

        foreach (Base::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);

                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }
                if ($curDelta === 0) {
                    break;
                }
            }
        }
        return $found;
    }

    public static function listAllFiles(string $dir): array
    {
        $array = scandir($dir);
        $result = [];

        foreach ($array as $value) {
            $currentPath = Path::join($dir, $value);

            if ($value === "." || $value === '..') {
                continue;
            } else if (is_file($currentPath)) {
                $result[] = $currentPath;
                continue;
            }

            foreach (self::listAllFiles($currentPath) as $_value) {
                $result[] = $_value;
            }
        }
        return $result;
    }

    public static function makeLightning(Entity $victim): void
    {
        $location = $victim->getLocation();
        $world = $location->getWorld();

        $light = new AddActorPacket();
        $light->actorUniqueId = Entity::nextRuntimeId();

        $light->actorRuntimeId = 1;
        $light->position = $location->asVector3();
        $light->type = "minecraft:lightning_bolt";
        $light->yaw = $location->getYaw();
        $light->syncedProperties = new PropertySyncData([], []);

        $world->addParticle($location, new BlockBreakParticle($world->getBlock($location->floor()->down())), $world->getPlayers());

        Base::getInstance()->getServer()->broadcastPackets($world->getPlayers(), [
            $light,
            PlaySoundPacket::create("ambient.weather.thunder", $location->getX(), $location->getY(), $location->getZ(), 1, 1)
        ]);
    }

    public static function arrayToPage(array $array, ?int $page, int $separator): array
    {
        $result = [];

        $pageMax = ceil(count($array) / $separator);
        $min = ($page * $separator) - $separator;

        $count = 1;
        $max = $min + $separator;

        foreach ($array as $item) {
            if ($count > $max) {
                continue;
            } else if ($count > $min) {
                $result[] = $item;
            }

            $count++;
        }
        return [$pageMax, $result];
    }

    public static function giveKit(Player $player): void
    {
        $session = Session::get($player);
        $kit = $session->data["kit"] ?? null;

        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);
        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2);

        $helmet = ($kit === "rainbow") ? IdsUtils::RAINBOW_HELMET : IdsUtils::IRIS_HELMET;
        $chestplate = ($kit === "rainbow") ? IdsUtils::RAINBOW_CHESTPLATE : IdsUtils::IRIS_CHESTPLATE;
        $leggings = ($kit === "rainbow") ? IdsUtils::RAINBOW_LEGGINGS : IdsUtils::IRIS_LEGGINGS;
        $boots = ($kit === "rainbow") ? IdsUtils::RAINBOW_BOOTS : IdsUtils::IRIS_BOOTS;
        $sword = ($kit === "rainbow") ? IdsUtils::RAINBOW_SWORD : IdsUtils::IRIS_SWORD;

        $items = [
            ItemFactory::getInstance()->get($helmet)->addEnchantment($unbreaking)->addEnchantment($protection),
            ItemFactory::getInstance()->get($chestplate)->addEnchantment($unbreaking)->addEnchantment($protection),
            ItemFactory::getInstance()->get($leggings)->addEnchantment($unbreaking)->addEnchantment($protection),
            ItemFactory::getInstance()->get($boots)->addEnchantment($unbreaking)->addEnchantment($protection),
            ItemFactory::getInstance()->get($sword)->addEnchantment($sharpness)->addEnchantment($unbreaking),
            ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 16),
            ItemFactory::getInstance()->get(ItemIds::SPLASH_POTION, 22, 40)
        ];

        foreach ($items as $item) {
            if ($item instanceof Armor) {
                $player->getArmorInventory()->setItem($item->getArmorSlot(), $item);
                continue;
            }

            $player->getInventory()->addItem($item);
        }

        if ($player->getNetworkSession()->getPlayerInfo()->getExtraData()["CurrentInputMode"] === 2) {
            $player->getInventory()->setItem(2, ItemFactory::getInstance()->get(ItemIds::NAUTILUS_SHELL));
        }

        $player->getEffects()->add(new EffectInstance(VanillaEffects::SPEED(), 20 * 60 * 60, 0, false));
        $player->getEffects()->add(new EffectInstance(VanillaEffects::STRENGTH(), 20 * 60 * 60, 0, false));
    }

    public static function allSelectorExecute(CommandSender $sender, string $command, array $args): void
    {
        if (!$sender->hasPermission("pocketmine.group.operator")) {
            $sender->sendMessage(Util::PREFIX . "Vous n'avez pas la permission de faire cela");
            return;
        }

        foreach (Base::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $cmd = $command . " " . implode(" ", $args);
            $cmd = str_replace("@a", "\"" . $player->getName() . "\"", $cmd);

            self::executeCommand($cmd);
        }
    }

    public static function executeCommand(string $command): void
    {
        $server = Base::getInstance()->getServer();
        $server->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), $command);
    }

    public static function getItemCount(Player $player, int $id, int $meta = 0): int
    {
        $count = 0;

        foreach ($player->getInventory()->getContents() as $item) {
            if ($item->getId() === $id && $item->getMeta() === $meta) {
                $count += $item->getCount();
            }
        }
        return $count;
    }

    public static function insideZone(Position $position, string $zone): bool
    {
        list ($x1, $y1, $z1, $x2, $y2, $z2, $world) = explode(":", Cache::$config["zones"][$zone]);

        $minX = min($x1, $x2);
        $minY = min($y1, $y2);
        $minZ = min($z1, $z2);

        $maxX = max($x1, $x2);
        $maxY = max($y1, $y2);
        $maxZ = max($z1, $z2);

        $x = $position->getFloorX();
        $y = $position->getFloorY();
        $z = $position->getFloorZ();

        return $x >= $minX && $x <= $maxX && $y >= $minY && $y <= $maxY && $z >= $minZ && $z <= $maxZ && $position->getWorld()->getFolderName() === $world;
    }

    public static function refresh(Player $player, bool $teleport = false): void
    {
        if ($teleport) {
            $player->teleport(Base::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        }

        $player->getXpManager()->setXpAndProgress(0, 0);
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getEffects()->clear();
        $player->setHealth(20);

        if (Session::get($player)->data["nightvision"] ?? false) {
            $player->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 107374182, 0, false));
        }
    }

    public static function giveItems(Player $player): void
    {
        $item = ItemFactory::getInstance()->get(ItemIds::MINECART_WITH_CHEST);
        $item->setCustomName("§r" . Util::PREFIX . "Kit §l§9«");
        $player->getInventory()->setItem(1, $item);

        $item = ItemFactory::getInstance()->get(ItemIds::REPEATER);
        $item->setCustomName("§r" . Util::PREFIX . "Paramètres §l§9«");
        $player->getInventory()->setItem(2, $item);

        $item = ItemFactory::getInstance()->get(ItemIds::FIREWORKS);
        $item->setCustomName("§r" . Util::PREFIX . "Tournoi §l§9«");
        $player->getInventory()->setItem(4, $item);
    }

    public static function getFile($name): Config
    {
        return new Config(Base::getInstance()->getDataFolder() . "data/" . $name . ".json", Config::JSON);
    }
}