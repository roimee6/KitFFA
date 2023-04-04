<?php /** @noinspection PhpUnused */

namespace NCore\command\player\util;

use CortexPE\Commando\BaseCommand;
use jojoe77777\FormAPI\SimpleForm;
use NCore\Session;
use NCore\Util;
use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use Util\util\IdsUtils;

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

    public static function getKits(): array
    {
        $unbreaking = new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3);
        $protection = new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2);
        $sharpness = new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2);

        return [
            "rainbow" => [
                "items" => [
                    ItemFactory::getInstance()->get(ItemIds::LEATHER_HELMET)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(ItemIds::LEATHER_CHESTPLATE)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(ItemIds::LEATHER_LEGGINGS)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(ItemIds::LEATHER_BOOTS)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(ItemIds::WOODEN_SWORD)->addEnchantment($sharpness)->addEnchantment($unbreaking),
                    ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 16),
                    ItemFactory::getInstance()->get(ItemIds::SPLASH_POTION, 22, 40)
                ],
                "effects" => [
                    new EffectInstance(VanillaEffects::SPEED(), 20 * 60 * 60, 0, false),
                    new EffectInstance(VanillaEffects::STRENGTH(), 20 * 60 * 60, 0, false)
                ]
            ],
            "sumo" => [
                "items" => [
                    ItemFactory::getInstance()->get(ItemIds::STEAK, 0, 64)
                ],
                "effects" => [
                    new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 60 * 5, 255, false)
                ]
            ],
            "iris" => [
                "items" => [
                    ItemFactory::getInstance()->get(IdsUtils::IRIS_HELMET)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(IdsUtils::IRIS_CHESTPLATE)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(IdsUtils::IRIS_LEGGINGS)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(IdsUtils::IRIS_BOOTS)->addEnchantment($unbreaking)->addEnchantment($protection),
                    ItemFactory::getInstance()->get(IdsUtils::IRIS_SWORD)->addEnchantment($sharpness)->addEnchantment($unbreaking),
                    ItemFactory::getInstance()->get(ItemIds::ENDER_PEARL, 0, 16),
                    ItemFactory::getInstance()->get(ItemIds::SPLASH_POTION, 22, 40)
                ],
                "effects" => [
                    new EffectInstance(VanillaEffects::SPEED(), 20 * 60 * 60, 0, false),
                    new EffectInstance(VanillaEffects::STRENGTH(), 20 * 60 * 60, 0, false)
                ]
            ]
        ];
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
                        $player->sendMessage(Util::PREFIX . "Pour avoir accès au kit §9rainbow §fvous devez être dans les §910% §fdes meilleurs joueurs du serveur, vous êtes actuellement dans les §9" . $percentage . "§f% !");
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