<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use jojoe77777\FormAPI\CustomForm;
use NCore\handler\ScoreFactory;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Setting extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "setting",
            ""
        );
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            $session = Session::get($sender);

            $form = new CustomForm(function (Player $player, mixed $data) use ($session) {
                if (!is_array($data)) {
                    return;
                }

                foreach ($data as $key => $value) {
                    if (!is_bool($value)) {
                        continue;
                    }

                    $session->data[$key] = $value;
                }

                ScoreFactory::removeScore($player);
                ScoreFactory::updateScoreboard($player);

                $player->sendMessage(Util::PREFIX . "Vous venez de mettre à jour vos paramètres !");
            });
            $form->setTitle("Paramètres");
            foreach (["scoreboard", "combo", "cps"] as $value) {
                $form->addToggle(Util::PREFIX . ucfirst($value), $session->data[$value] ?? false, $value);
            }
            $sender->sendForm($form);
        }
    }

    protected function prepare(): void
    {
    }
}