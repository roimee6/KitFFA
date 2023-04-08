<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\server;

use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\command\sub\OptionArgument;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\entity\Location;
use pocketmine\plugin\Plugin;
use Util\entity\LeaderboardEntity;

class FloatingEntity extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "floating",
            "Fait disparaitre ou apparaitre les floatings texts"
        );

        $this->setPermission("pocketmine.group.operator");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        switch ($args["opt"]) {
            case "spawn":
                $defaultWorld = Base::getInstance()->getServer()->getWorldManager()->getDefaultWorld();

                $entity = new LeaderboardEntity(new Location(3.5, 66.5, -3.5, $defaultWorld, 0, 0));
                $entity->spawnToAll();

                $sender->sendMessage(Util::PREFIX . "Vous venez de faire apparaitre les floatings texts");
                break;
            case "despawn":
                foreach (Base::getInstance()->getServer()->getWorldManager()->getWorlds() as $world) {
                    foreach ($world->getEntities() as $entity) {
                        if ($entity instanceof LeaderboardEntity) {
                            $entity->flagForDespawn();
                        }
                    }
                }

                $sender->sendMessage(Util::PREFIX . "Vous venez de supprimer les floatings texts");
                break;
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new OptionArgument("opt", ["spawn", "despawn"]));
    }
}