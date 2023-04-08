<?php

namespace Util\entity;

use NCore\command\player\util\Top;
use NCore\Util;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class LeaderboardEntity extends Living
{
    private int $currentCategory = 0;

    private array $category = [
        "kill",
        "elo",
        "killstreak"
    ];

    protected $gravityEnabled = false;
    protected $gravity = 0.0;
    private int $tickToUpdate;

    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);
        $this->setNameTagAlwaysVisible();
        $this->setNameTag($this->getUpdate());
        $this->setScale(0.001);
        $this->setImmobile();

        $this->tickToUpdate = 300;
    }

    private function getUpdate(): string
    {
        $this->currentCategory++;
        $i = 1;

        if (!isset($this->category[$this->currentCategory])) {
            $this->currentCategory = 0;
        }

        $format = "§7{COUNT}. §9{KEY} §8(§f{VALUE}§8)";
        $name = $this->category[$this->currentCategory];

        $top = Top::getPlayersTopList($name);
        $response = Util::arrayToPage($top, 1, 10);

        $nametag = Top::getTopName($name);

        foreach ($response[1] as $value) {
            $nametag .= "\n" .str_replace(["{KEY}", "{VALUE}", "{COUNT}"], [$value[0], $value[1], $i], $format);
            $i++;
        }
        return $nametag;
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::CHICKEN;
    }

    public function getName(): string
    {
        return "LeaderboardEntity";
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
    }

    public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void
    {
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.7, 0.4);
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->isClosed()) {
            return false;
        }

        if ($this->isAlive()) {
            --$this->tickToUpdate;

            if ($this->tickToUpdate <= 0) {
                $this->setNameTag($this->getUpdate());
                $this->tickToUpdate = 300;
            }
        }
        return parent::entityBaseTick($tickDiff);
    }
}