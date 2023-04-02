<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use jojoe77777\FormAPI\SimpleForm;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Kit extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "kit",
            "Choisissez votre kit lorsque vous quitter le spawn"
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);
            $kit = $session->data["kit"] ?? null;

            $default = is_null($kit) ? "Iris\n§aActuel" : "Iris";
            $rainbow = ($kit === "rainbow") ? "Rainbow\n§aActuel" : "Rainbow";

            $form = new SimpleForm(function (Player $player, mixed $data) use ($session) {
                if (!is_string($data)) {
                    return;
                }

                if ($data !== "rainbow") {
                    $session->data["kit"] = null;
                    $player->sendMessage(Util::PREFIX . "Votre kit est désormais le kit par défaut !");
                } else {
                    $percentage = $session->getPercentage();

                    if ($percentage > 10) {
                        $player->sendMessage(Util::PREFIX . "Pour avoir accès au kit §9rainbow §fvous devez être dans les §910% §fdes meilleurs joueurs du serveur, vous êtes actuellement dans les §9" . $percentage. "§f% !");
                        return;
                    }

                    $session->data["kit"] = "rainbow";
                    $player->sendMessage(Util::PREFIX . "Votre kit est désormais le kit rainbow !");
                }
            });
            $form->setTitle("Kit");
            $form->setContent(Util::PREFIX . "Choisissez le kit de votre choix");
            $form->addButton($default, -1, "", "default");
            $form->addButton($rainbow, -1, "", "rainbow");
            $sender->sendForm($form);
        }
    }

    protected function prepare(): void
    {
    }
}