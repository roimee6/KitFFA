<?php

namespace Util\check;

use NCore\Session as CoreSession;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\player\Player;
use Util\Base;
use Util\session\Session;

abstract class Check
{
    public int $vl = 0;

    public function getName(): string
    {
        $array = explode("\\", get_class($this));
        return $array[count($array) - 1];
    }

    public abstract function getMaxViolations(): int;

    public abstract function checkEvent(Session $session, Event $ev): void;

    protected function checkPlayer(Player $player, Session $session): bool
    {
        $tick = Base::getInstance()->getServer()->getTick();

        if (
            0.1 > $player->getFallDistance() ||
            $this->checkPing($player) ||
            $player->isOnGround() ||
            $player->isUnderwater() ||
            $player->getAllowFlight() ||
            $player->isImmobile() ||
            $player->isFlying() ||
            $player->isCreative() ||
            $player->getEffects()->has(VanillaEffects::LEVITATION()) ||
            $tick - $session->lastPlace < 4 ||
            $tick - $session->lastDamaged < 4
        ) {
            return true;
        }
        return false;
    }

    protected function checkPing(Player $player): bool
    {
        $playerSession = CoreSession::get($player);

        $pings = $playerSession->data["ping"];
        $pings[] = 1;

        return max($pings) > 150;
    }

    public abstract function checkPacket(Session $session, DataPacket $pk): void;
}