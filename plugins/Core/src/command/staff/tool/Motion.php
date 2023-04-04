<?php /** @noinspection PhpUnused */

namespace NCore\command\staff\tool;

use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

class Motion extends BaseCommand
{
    public function __construct(Plugin $plugin)
    {
        parent::__construct(
            $plugin,
            "motion",
            "Commande pour maxooozinho"
        );

        $this->setPermission("staff.group");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $x = $args["x"];
        $y = $args["y"];
        $z = $args["z"];

        if ($sender instanceof Player) {
            $sender->setMotion(new Vector3($x, $y, $z));
        }
    }

    protected function prepare(): void
    {
        $this->registerArgument(0, new IntegerArgument("x"));
        $this->registerArgument(1, new IntegerArgument("y"));
        $this->registerArgument(2, new IntegerArgument("z"));
    }
}