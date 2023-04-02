<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\command\sub\OptionArgument;
use NCore\command\sub\TargetArgument;
use NCore\handler\Cache;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class Buy extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "buy",
            "Ajoute un grade à un joueur ou des gemmes avec un message"
        );

        $this->setPermission("pocketmine.group.operator");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (isset($args["gemme"])) {
            Util::executeCommand("addvalue \"" . $args["joueur"] . "\" " . $args["gemme"] . " gem");
            Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "§9Le joueur §f" . $args["joueur"] . " §9vient d'acheter §f" . $args["gemme"] . " §9gemmes sur la boutique !! §fhttps://shop.nitrofaction.fr");
        } else if (isset($args["grade"])) {
            Util::executeCommand("setrank \"" . $args["joueur"] . "\" " . $args["grade"]);
            Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "§9Le joueur §f" . $args["joueur"] . " §9vient d'acheter le grade §f" . $args["grade"] . " §9sur la boutique !! §fhttps://shop.nitrofaction.fr");
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
        $this->registerArgument(0, new RawStringArgument("joueur"));
        $this->registerArgument(1, new IntegerArgument("gemme"));
        $this->registerArgument(1, new OptionArgument("grade", array_keys(Cache::$config["ranks"])));
    }
}