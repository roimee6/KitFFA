<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\sanction;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use NCore\Base;
use NCore\handler\Cache;
use NCore\handler\discord\Discord;
use NCore\handler\discord\EmbedBuilder;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Unban extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "unban",
            "Permet de débannir les joueurs banni"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $username = strtolower($args["joueur"]);

        if ($sender instanceof Player && Session::get($sender)->data["rank"] === "guide") {
            $sender->sendMessage(Util::PREFIX . "Vous n'avez pas la permission de faire cela");
            return;
        }

        if (!isset(Cache::$players["upper_name"][$username])) {
            if ($username === "all" && $sender->getName() === "MaXoooZ") {
                Cache::$bans = [];
                $sender->sendMessage(Util::PREFIX . "Vous venez de débannir tous les joueurs du serveur");
                return;
            }

            $sender->sendMessage(Util::PREFIX . "Vous venez de supprimer la valeur §9" . $username . " §fde la liste des bans");
            Base::getInstance()->getServer()->getNetwork()->unblockAddress($username);

            unset(Cache::$bans[$username]);
            return;
        }

        $file = Util::getFile("players/" . $username);
        $data = $file->getAll();

        unset(Cache::$bans[$username]);

        foreach (Cache::$config["saves"] as $column) {
            foreach ($data[$column] as $datum) {
                unset(Cache::$bans[$datum]);
                Base::getInstance()->getServer()->getNetwork()->unblockAddress($datum);
            }
        }

        Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le staff §9" . $sender->getName() . " §fvient de débannir le joueur §9" . $username);

        $embed = new EmbedBuilder();
        $embed->setDescription("**Unban**\n\n**Joueur**\n" . $username . "\n\n*Dé-Banni par le staff: " . $sender->getName() . "*");
        $embed->setColor(5635925);
        Discord::send($embed, Cache::$config["sanction_webhook"]);
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new RawStringArgument("joueur"));
    }
}