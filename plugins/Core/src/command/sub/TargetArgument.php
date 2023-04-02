<?php

namespace NCore\command\sub;

use CortexPE\Commando\args\BaseArgument;
use NCore\Base;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\player\Player;

class TargetArgument extends BaseArgument
{
    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }

    public function getTypeName(): string
    {
        return "target";
    }

    public function canParse(string $testString, CommandSender $sender): bool
    {
        return $testString === "@a" || Player::isValidUserName($testString);
    }

    public function parse(string $argument, CommandSender $sender): ?string
    {
        $player = Util::getPlayer($argument) ?? Base::getInstance()->getServer()->getOfflinePlayer($argument);
        return $player->getName();
    }
}