<?php

namespace ErosionYT\Crates;

use ErosionYT\Crates\provider\SQLiteProvider;
use ErosionYT\Crates\command\CommandManager;
use ErosionYT\Crates\crate\CrateManager;
use ErosionYT\Crates\session\SessionManager;
use pocketmine\plugin\PluginBase;

class Crates extends PluginBase {

    /** @var self */
    private static $instance;

    /** @var CrateManager */
    private $cratesManager;

    /** @var SessionManager */
    private $sessionManager;

    /** @var CommandManager */
    private $commandManager;

    /** @var SQLiteProvider */
    private $provider;

    public function onLoad() {
        if(!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }
        if(!is_dir($this->getDataFolder() . "crates")) {
            mkdir($this->getDataFolder() . "crates");
        }
        $this->saveConfig();
        $this->saveResource("crates" . DIRECTORY_SEPARATOR . "vote.yml");
        $this->saveResource("crates" . DIRECTORY_SEPARATOR . "common.yml");
        $this->saveResource("crates" . DIRECTORY_SEPARATOR . "uncommon.yml");
        $this->saveResource("crates" . DIRECTORY_SEPARATOR . "rare.yml");
        $this->saveResource("crates" . DIRECTORY_SEPARATOR . "mythic.yml");
        self::$instance = $this;
    }

    public function onEnable() {
        $this->provider = new SQLiteProvider($this);
        $this->cratesManager = new CrateManager($this);
        $this->sessionManager = new SessionManager($this);
        $this->commandManager = new CommandManager($this);
        $this->getServer()->getPluginManager()->registerEvents(new CratesListener($this), $this);
    }

    /**
     * @param array $keys
     *
     * @return string
     */
    public function encodeKeys(array $keys): string {
        $crates = [];
        foreach($keys as $id => $amount) {
            $crates[] = "$id:$amount";
        }
        return implode(",", $crates);
    }

    /**
     * @param string $keys
     *
     * @return array
     */
    public function decodeKeys(string $keys): array {
        if(empty($keys)) {
            return [];
        }
        $crates = explode(",", $keys);
        $crateKeys = [];
        foreach($crates as $crate) {
            $parts = explode(":", $crate);
            $crateKeys[$parts[0]] = (int)$parts[1];
        }
        return $crateKeys;
    }

    /**
     * @return self
     */
    public static function getInstance(): self {
        return self::$instance;
    }

    /**
     * @return CrateManager
     */
    public function getCratesManager(): CrateManager {
        return $this->cratesManager;
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager(): SessionManager {
        return $this->sessionManager;
    }

    /**
     * @return CommandManager
     */
    public function getCommandManager(): CommandManager {
        return $this->commandManager;
    }

    /**
     * @return SQLiteProvider
     */
    public function getProvider(): SQLiteProvider {
        return $this->provider;
    }
}