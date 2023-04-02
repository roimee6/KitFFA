<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\handler\ScoreFactory;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Nightvision extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "nightvision",
            "Active ou désactive l'effet de nightvision"
        );

        $this->setAliases(["nv"]);
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if ($session->data["nightvision"] ?? false) {
                $session->data["nightvision"] = false;
                $sender->sendMessage(Util::PREFIX . "Vous ne possèdez plus l'effet de night vision");

                $sender->getEffects()->remove(VanillaEffects::NIGHT_VISION());
            } else {
                $session->data["nightvision"] = true;
                $sender->sendMessage(Util::PREFIX . "Vous possèdez desormais l'effet de nightvision");

                $sender->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 107374182, 0, false));
            }
        }
    }

    protected function prepare(): void
    {
    }
}