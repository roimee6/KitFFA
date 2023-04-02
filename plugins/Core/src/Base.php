<?php

namespace NCore;

use CortexPE\Commando\PacketHooker;
use NCore\command\CommandManager;
use NCore\handler\Cache;
use NCore\listener\PlayerListener;
use NCore\task\BaseTask;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;

class Base extends PluginBase
{
    use SingletonTrait;

    public function getFile(): string
    {
        return parent::getFile();
    }

    protected function onLoad(): void
    {
        date_default_timezone_set("Europe/Paris");
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        if (!PacketHooker::isRegistered()) PacketHooker::register($this);

        Cache::startup();
        CommandManager::startup();

        $this->getScheduler()->scheduleRepeatingTask(new BaseTask(), 1);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener(), $this);

        $this->getServer()->getWorldManager()->getDefaultWorld()->setTime(12750);
        $this->getServer()->getWorldManager()->getDefaultWorld()->stopTime();
        $this->getServer()->getWorldManager()->getDefaultWorld()->setChunkTickRadius(0);
    }

    protected function onDisable(): void
    {
        Cache::save();

        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $session = Session::get($player);
            $session->saveSessionData();
        }
    }
}