<?php

namespace Util\item\items;

use NCore\Util;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\Releasable;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\ThrowSound;
use Util\entity\SplashPotion as EntitySplashPotion;

class PotionLauncher extends Item implements Releasable
{
    private int $potionId = PotionTypeIds::STRONG_HEALING;

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult
    {
        if ($player->getNetworkSession()->getPlayerInfo()->getExtraData()["CurrentInputMode"] !== 2) {
            $player->sendMessage(Util::PREFIX . "Le potion launcher n'est disponible que pour les tactiles");
            return ItemUseResult::FAIL();
        } else if (1 > Util::getItemCount($player, ItemIds::SPLASH_POTION, $this->potionId)) {
            $player->sendMessage(Util::PREFIX . "Vous n'avez pas de potion dans votre inventaire");
            return ItemUseResult::FAIL();
        }

        $projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $player->getLocation()->yaw, $player->getLocation()->pitch), $player);
        $projectile->setMotion($directionVector->multiply($this->getThrowForce()));

        $projectileEv = new ProjectileLaunchEvent($projectile);
        $projectileEv->call();

        if ($projectileEv->isCancelled()) {
            $projectile->flagForDespawn();
            return ItemUseResult::FAIL();
        }

        $projectile->spawnToAll();

        $player->getLocation()->getWorld()->addSound($player->getLocation(), new ThrowSound());
        $player->getInventory()->removeItem(ItemFactory::getInstance()->get(ItemIds::SPLASH_POTION, $this->potionId));

        return ItemUseResult::SUCCESS();
    }

    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        return new EntitySplashPotion($location, $thrower, PotionTypeIdMap::getInstance()->fromId($this->potionId));
    }

    public function getThrowForce(): float
    {
        return 0.5;
    }

    public function canStartUsingItem(Player $player): bool
    {
        return false;
    }
}