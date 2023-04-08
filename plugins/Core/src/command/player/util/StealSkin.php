<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\command\sub\TargetArgument;
use NCore\handler\RankAPI;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class StealSkin extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "stealskin",
            "Vole le skin d'un autre joueur"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $target = Util::getPlayer($args["joueur"]);

            if (!RankAPI::hasRank($sender, "master")) {
                $sender->sendMessage(Util::PREFIX . "Vous n'avez pas la permission de faire cela");
                return;
            } else if (!$target instanceof Player) {
                $sender->sendMessage(Util::PREFIX . "Le joueur n'éxiste pas ou n'est pas connecté sur le serveur");
                return;
            }

            $sender->setSkin($target->getSkin());
            $sender->sendSkin();

            $sender->sendMessage(Util::PREFIX . "Vous venez de voler le skin de §9" . $target->getName());
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new TargetArgument("joueur"));
    }
}