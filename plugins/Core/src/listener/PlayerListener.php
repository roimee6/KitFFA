<?php /** @noinspection PhpUnused */

namespace NCore\listener;

use NCore\Base;
use NCore\command\staff\sanction\Ban;
use NCore\command\staff\tool\Vanish;
use NCore\handler\RankAPI;
use NCore\handler\SanctionAPI;
use NCore\Session;
use NCore\Session as CoreSession;
use NCore\task\BaseTask;
use NCore\Util;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDataSaveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\EnderPearl;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class PlayerListener implements Listener
{
    /**
     * @handleCancelled
     */
    public function onUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        $session = Session::get($player);

        if ($session->data["staff_mod"][0]) {
            switch ($item->getCustomName()) {
                case "§r" . Util::PREFIX . "Vanish §9§l«":
                    $player->chat("/vanish");
                    break;
                case "§r" . Util::PREFIX . "Random Tp §9§l«":
                    $player->chat("/randomtp");
                    break;
                case "§r" . Util::PREFIX . "Spectateur §9§l«":
                    $player->chat("/spec");
                    break;
            }
        }

        if ($item->getId() === ItemIds::REPEATER) {
            $player->chat("/setting");
        } else if ($item->getId() === ItemIds::MINECART_WITH_CHEST) {
            $player->chat("/kit");
        }

        if ($item instanceof EnderPearl) {
            if ($session->inCooldown("enderpearl")) {
                $player->sendMessage(Util::PREFIX . "Veuillez attendre §9" . round($session->getCooldownData("enderpearl")[0] - time()) . " §fsecondes avant de relancer une nouvelle perle");
                $event->cancel();
            } else {
                $session->setCooldown("enderpearl", 15, [], true);
                BaseTask::$enderpearl[] = $player->getName();
            }
        }
    }

    public function onChat(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        $session = Session::get($player);

        if ($session->inCooldown("chat")) {
            $event->cancel();
        } else {
            if (!$player->hasPermission("pocketmine.group.operator")) {
                $session->setCooldown("chat", 2);
            }
        }

        if (($session->data["staff_chat"] || $event->getMessage()[0] === "!") && $player->hasPermission("staff.group")) {
            if (!$session->data["staff_chat"]) {
                $message = substr($message, 1);
            }

            $event->cancel();

            foreach (Base::getInstance()->getServer()->getOnlinePlayers() as $target) {
                if ($target->hasPermission("staff.group")) {
                    $target->sendMessage("§f[§9S§f] [§9StaffChat§f] §9" . $player->getName() . " " . Util::PREFIX . $message);
                }
            }

            Base::getInstance()->getLogger()->info("[S] [StaffChat] " . $player->getName() . " » " . $message);
        } else if ($session->inCooldown("mute")) {
            $player->sendMessage(Util::PREFIX . "Vous êtes mute, temps restant: §9" . SanctionAPI::format($session->getCooldownData("mute")[0] - time()));
            $event->cancel();
        } else if (!$player->hasPermission("pocketmine.group.operator") && str_contains($message, "@here")) {
            $player->sendMessage(Util::PREFIX . "Votre message ne peut pas contenir §9\"@here\"");
            $event->cancel();
        }

        if (!$event->isCancelled()) {
            if (!RankApi::hasRank($player, "roi")) {
                $message = TextFormat::clean($message);
            }

            if ($message === "") {
                $event->cancel();
                return;
            }

            $rank = ($player->getName() === $player->getDisplayName()) ? RankAPI::getRank($player->getName()) : "joueur";
            $event->setFormat(RankAPI::setReplace(RankAPI::getRankValue($rank, "chat"), $player, $message));
        }
    }

    public function onJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Session::get($player);

        $event->setJoinMessage("");
        $player->setViewDistance(8);

        if (Ban::checkBan($event)) {
            return;
        }

        Base::getInstance()->getServer()->broadcastPopup("§a+ " . $player->getName() . " +");

        foreach (Vanish::$vanish as $target) {
            $target = Util::getPlayer($target);

            if ($target instanceof Player) {
                if ($target->hasPermission("staff.group") || $target->getName() === $player->getName()) {
                    continue;
                }
                $target->hidePlayer($player);
            }
        }

        if ($session->data["staff_mod"][0] && $player->getGamemode() === GameMode::SURVIVAL()) {
            $player->setAllowFlight(true);
        }

        RankAPI::updateNameTag($player);
        RankAPI::addPermissions($player);

        Util::refresh($player, true);
        Util::giveItems($player);
    }

    public function onQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Session::get($player);

        $event->setQuitMessage("");

        if ($session->inCooldown("combat")) {
            $player->kill();
        }

        Base::getInstance()->getServer()->broadcastPopup("§c- " . $player->getName() . " -");
        $session->saveSessionData();
    }

    public function onDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $session = Session::get($player);

        $event->setDeathMessage("");

        $session->removeCooldown("combat");
        $session->removeCooldown("enderpearl");

        $session->setValue("killstreak", 0);
        $session->addValue("death", 1);

        $event->setXpDropAmount(0);
        $event->setDrops([]);

        $cause = $player->getLastDamageCause();

        if (!is_null($cause) && $cause->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $damager = $cause->getDamager();

            if ($cause instanceof EntityDamageByEntityEvent && $damager instanceof Player) {
                $damagerSession = Session::get($damager);

                $pot1 = Util::getItemCount($player, ItemIds::SPLASH_POTION, 22);
                $pot2 = Util::getItemCount($damager, ItemIds::SPLASH_POTION, 22);

                Base::getInstance()->getLogger()->info($player->getDisplayName() . " (" . $player->getName() . ") a été tué par " . $damager->getDisplayName() . " (" . $damager->getName() . ")");
                Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "§9" . $player->getDisplayName() . "[§7" . $pot1 . "§9] §fa été tué par le joueur §9" . $damager->getDisplayName() . "[§7" . $pot2 . "§9]");

                $damagerSession->removeCooldown("combat");

                $damagerSession->addValue("kill", 1);
                $damagerSession->addValue("killstreak", 1);

                if ($damagerSession->data["killstreak"] % 5 == 0) {
                    Base::getInstance()->getServer()->broadcastMessage(Util::PREFIX . "Le joueur §9" . $damager->getName() . " §fa fait §9" . $damagerSession->data["killstreak"] . " §fkill sans mourrir !");
                }

                $multiplier = ($damagerSession->data["killstreak"] * 3 / 100 + 1);
                $damagerSession->addValue("elo", mt_rand(3, 10) * $multiplier);

                Util::refresh($damager);
                Util::giveKit($damager);
            }
        }
    }

    public function onPlayerSave(PlayerDataSaveEvent $event): void
    {
        $player = $event->getPlayer();

        if ($player instanceof Player) {
            $session = Session::get($player);
            $session->saveSessionData();
        }
    }

    /**
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    public function onDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if ($event->getModifier(EntityDamageEvent::MODIFIER_PREVIOUS_DAMAGE_COOLDOWN) < 0.0) {
            $event->cancel();
        }

        if ($entity instanceof Player) {
            $entitySession = Session::get($entity);

            if ($event->getCause() === EntityDamageEvent::CAUSE_FALL || Util::insideZone($entity->getPosition(), "spawn") || $entitySession->data["staff_mod"][0]) {
                $event->cancel();
            }

            if ($event instanceof EntityDamageByEntityEvent && ($damager = $event->getDamager()) instanceof Player) {
                $damagerSession = Session::get($damager);

                if (Util::insideZone($damager->getPosition(), "spawn")) {
                    $event->cancel();
                }

                if ($damagerSession->inCooldown("combat")) {
                    $combat = $damagerSession->getCooldownData("combat")[1];

                    if ($combat !== $entity->getName()) {
                        $damager->sendMessage(Util::PREFIX . "Vous êtes déjà en combat face à §9" . $combat);
                        $event->cancel();
                    }
                }

                if ($entitySession->inCooldown("combat")) {
                    $combat = $entitySession->getCooldownData("combat")[1];

                    if ($combat !== $damager->getName()) {
                        $damager->sendMessage(Util::PREFIX . "Le joueur §9" . $entity->getName() . " §fest déjà en combat");
                        $event->cancel();
                    }
                }

                if ($damagerSession->data["staff_mod"][0]) {
                    switch ($damager->getInventory()->getItemInHand()->getCustomName()) {
                        case "§r" . Util::PREFIX . "Sanction §9§l«":
                            SanctionAPI::chooseSanction($damager, strtolower($entity->getName()));
                            break;
                        case "§r" . Util::PREFIX . "Alias §9§l«":
                            $damager->chat("/alias \"" . $entity->getName() . "\"");
                            break;
                        case "§r" . Util::PREFIX . "Freeze §9§l«":
                            $damager->chat("/freeze \"" . $entity->getName() . "\"");
                            break;
                        case "§r" . Util::PREFIX . "Knockback 2 §9§l«":
                            return;
                        default:
                            $damager->sendMessage(Util::PREFIX . "Vous venez de taper le joueur §9" . $entity->getName());
                            break;
                    }

                    $event->cancel();
                }

                if ($event->isCancelled() || $entity->getGamemode() === GameMode::CREATIVE() || $damager->getGamemode() === GameMode::CREATIVE() || $entity->isImmobile() || $entity->isFlying() || $entity->getAllowFlight()) {
                    return;
                }

                $damagerSession->addValue("combo_count", 1);
                $entitySession->setValue("combo_count", 0);

                $damagerSession->setCooldown("combat", 30, [$entity->getName()]);
                $entitySession->setCooldown("combat", 30, [$damager->getName()]);

                $event->setKnockback(0.38);
                $event->setAttackCooldown(8.60);
            }
        }
    }

    public function onCommand(CommandEvent $event): void
    {
        $sender = $event->getSender();

        $command = explode(" ", $event->getCommand());
        Base::getInstance()->getLogger()->info("[" . $sender->getName() . "] " . implode(" ", $command));

        if ($sender instanceof Player) {
            $session = Session::get($sender);

            if ($session->inCooldown("cmd")) {
                $event->cancel();
            } else {
                if (!$sender->hasPermission("pocketmine.group.operator")) {
                    $session->setCooldown("cmd", 1);
                }
            }

            if ($sender->isImmobile()) {
                $event->cancel();
                return;
            }

            $command[0] = strtolower($command[0]);
            $event->setCommand(implode(" ", $command));
        }
    }

    public function onPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();

        if (Session::get($player)->data["staff_mod"][0] || $player->getGamemode() !== GameMode::CREATIVE() || !$player->hasPermission("pocketmine.group.operator")) {
            $event->cancel();
        }
    }

    public function onBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();

        if (Session::get($player)->data["staff_mod"][0] || $player->getGamemode() !== GameMode::CREATIVE() || !$player->hasPermission("pocketmine.group.operator")) {
            $event->cancel();
        }
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        $session = $event->getOrigin();

        $player = $session->getPlayer();

        if ($player instanceof Player) {
            if ($packet instanceof AnimatePacket && $packet->action === AnimatePacket::ACTION_SWING_ARM) {
                $event->cancel();
                $player->getServer()->broadcastPackets($player->getViewers(), [$packet]);
            }
        }
    }
}