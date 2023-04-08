<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use NCore\handler\SanctionAPI;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Master extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "master",
            "Regarde le temps qu'il te reste avant que tu perdre ton grade master"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if (isset($session->data["master"])) {
                $time = max(time(), $session->data["master"]);
                $format = SanctionAPI::format($time - time());

                $sender->sendMessage(Util::PREFIX . "Votre grade master s'enlevera dans: §9" . $format);
            } else {
                if ($session->data["rank"] === "master") {
                    $sender->sendMessage(Util::PREFIX . "Il semble que votre grade master est à vie !");
                } else {
                    $sender->sendMessage(Util::PREFIX . "Vous ne possedez pas de grade master");
                }
            }
        }
    }

    protected function prepare(): void
    {
    }
}