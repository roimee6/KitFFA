<?php

namespace Util\session;

use NCore\handler\SanctionAPI;
use NCore\Util;
use pocketmine\entity\effect\Effect;
use pocketmine\event\Event;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;
use Util\Base;
use Util\check\Check;
use Util\check\checks\combat\autoclicker\AutoClickerA;
use Util\check\checks\movement\airjump\AirJumpA;
use Util\check\checks\movement\fly\FlyA;
use Util\check\checks\movement\fly\FlyB;
use Util\check\checks\movement\fly\FlyC;
use Util\check\checks\movement\fly\FlyD;
use Util\session\processors\MoveProcessor;
use WeakMap;

class Session
{
    /** @phpstan-var WeakMap<Player, Session> */
    private static WeakMap $sessions;

    public MoveProcessor $moveProcessor;
    public ?Position $lastLocation = null;

    public array $clicks = [];

    public float $blocksInAir = 0;
    public float $deltaY = 0;
    public float $lastDeltaY = 0;

    public int $liquidTicks = 0;
    public int $groundTicks = 0;
    public int $iceTicks = 0;
    public int $climbableTicks = 0;

    public int $lastPlace = 0;
    public int $lastDamaged = 0;

    public bool $nearGround = true;

    private array $checks = [];
    private array $checkVl = [];

    public function __construct(private Player $player)
    {
        $this->moveProcessor = new MoveProcessor($this);

        $this->checks["AirJumpA"] = new AirJumpA();
        $this->checks["AutoClickerA"] = new AutoClickerA();
        $this->checks["FlyA"] = new FlyA();
        $this->checks["FlyB"] = new FlyB();
        $this->checks["FlyC"] = new FlyC();
        $this->checks["FlyD"] = new FlyD();
    }

    public static function get(Player $player): Session
    {
        self::$sessions ??= new WeakMap();
        return self::$sessions[$player] ??= new Session($player);
    }

    public function performCheck(Event|DataPacket $data): void
    {
        foreach ($this->checks as $check) {
            if ($data instanceof Event) {
                $check->checkEvent($this, $data);
            } else if ($data instanceof DataPacket) {
                $check->checkPacket($this, $data);
            }
        }
    }

    public function increaseVl(Check $check): void
    {
        if (!isset($this->checkVl[$check->getName()])) {
            $this->checkVl[$check->getName()] = 0;
        }

        $this->checkVl[$check->getName()] += 1;

        if ($this->checkVl[$check->getName()] < $check->getMaxViolations()) {
            $this->alert($check);
        }

        if ($this->checkVl[$check->getName()] >= $check->getMaxViolations()) {
            $this->checkVl[$check->getName()] = 0;
            SanctionAPI::banPlayer("AntiCheat", $this->getPlayer()->getName(), substr($check->getName(), 0, -1), 20 * 24 * 60 * 60);
        }
    }

    public function alert(Check $check): void
    {
        foreach (Base::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if ($player->hasPermission("staff.group")) {
                Base::getInstance()->getLogger()->info($this->getPlayer()->getName() . ": " . $check->getName() . " [vl=" . $this->checkVl[$check->getName()] . "]");
                $player->sendMessage(Util::PREFIX . "Detection: §9" . $this->getPlayer()->getName() . "§f, " . $check->getName() . "[vl=§9" . $this->checkVl[$check->getName()] . "§f]");
            }
        }
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function inVoid(): bool
    {
        return $this->getPlayer()->getPosition()->getY() <= 0;
    }
}
