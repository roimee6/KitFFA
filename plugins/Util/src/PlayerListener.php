<?php /** @noinspection PhpUnused */

namespace Util;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\player\Player;
use Util\session\Session;

class PlayerListener implements Listener
{
    public function onMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Session::get($player);

        $session->moveProcessor->process($event);

        if (Base::getInstance()->canPerformCheck()) {
            $session->performCheck($event);
        }
    }

    public function onPacketReceive(DataPacketReceiveEvent $event): void
    {
        $player = $event->getOrigin()->getPlayer();

        if ($player instanceof Player) {
            $session = Session::get($player);
            $packet = $event->getPacket();

            if ($packet instanceof DataPacket && Base::getInstance()->canPerformCheck()) {
                $session->performCheck($packet);
            }
        }
    }

    /**
     * @priority LOWEST
     */
    public function onPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Session::get($player);

        $session->lastPlace = Base::getInstance()->getServer()->getTick();
    }


    /**
     * @priority LOWEST
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $player = $event->getEntity();

        if ($player instanceof Player) {
            $session = Session::get($player);
            $session->lastDamaged = Base::getInstance()->getServer()->getTick();
        }
    }

    public function onJump(PlayerJumpEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Session::get($player);

        if (Base::getInstance()->canPerformCheck()) {
            $session->performCheck($event);
        }
    }

    public function onItemSpawn(ItemSpawnEvent $event): void
    {
        $entity = $event->getEntity();
        $entity->setDespawnDelay(5 * Base::getInstance()->getServer()->getTicksPerSecondAverage());
    }
}