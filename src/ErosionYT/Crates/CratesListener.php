<?php

declare(strict_types = 1);

namespace ErosionYT\Crates;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Player;

class CratesListener implements Listener {

    /** @var Crates */
    private $plugin;

    /**
     * CrateListener constructor.
     *
     * @param Crates $plugin
     */
    public function __construct(Crates $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @priority NORMAL
     * @param PlayerLoginEvent $event
     */
    public function onPlayerLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof Player) {
            return;
        }
        $provider = Crates::getInstance()->getProvider();
        if(!$provider->isRegistered($player)) {
            $provider->register($player);
        }
    }

    /**
     * @priority NORMAL
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof Player) {
            return;
        }
        $this->plugin->getSessionManager()->getSession($player);
        $provider = Crates::getInstance()->getProvider();
        if(!$provider->isRegistered($player)) {
            $provider->register($player);
        }
        foreach($this->plugin->getCratesManager()->getCrates() as $crate) {
            $crate->spawnTo($player);
        }
    }

    /**
     * @priority LOWEST
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        if(!$player instanceof Player) {
            return;
        }
        $block = $event->getBlock();
        foreach($this->plugin->getCratesManager()->getCrates() as $crate) {
            if($crate->getPosition()->equals($block->asPosition())) {
                $crate->try($player);
                $event->setCancelled();
            }
        }
    }
}