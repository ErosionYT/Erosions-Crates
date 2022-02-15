<?php

namespace ErosionYT\Crates\session;

use ErosionYT\Crates\crate\Crate;
use ErosionYT\Crates\Crates;
use pocketmine\player\Player;

class SessionManager {

    /** @var Crate */
    private $plugin;

    /** @var Session */
    private $sessions;

    /**
     * SessionManager constructor.
     *
     * @param Crates $plugin
     */
    public function __construct(Crates $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * @param Player $player
     *
     * @return Session
     */
    public function createSession(Player $player): Session {
        $session = new Session($player);
        $this->sessions[$player->getRawUniqueId()] = $session;
        return $session;
    }

    /**
     * @param Player $player
     *
     * @return Session
     */
    public function getSession(Player $player): Session {
        if(!isset($this->sessions[$player->getRawUniqueId()])) {
            return $this->createSession($player);
        }
        return $this->sessions[$player->getRawUniqueId()];
    }

    /**
     * @param Player $player
     */
    public function deleteSession(Player $player) {
        unset($this->sessions[$player->getRawUniqueId()]);
    }
}