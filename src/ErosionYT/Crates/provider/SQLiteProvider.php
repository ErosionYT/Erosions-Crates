<?php

namespace ErosionYT\Crates\provider;

use ErosionYT\Crates\Crates;
use pocketmine\Player;
use SQLite3;
use function sqlite_fetch_column_types;

class SQLiteProvider {

    /** @var Crates */
    private $plugin;

    /** @var SQLite3 */
    private $database;

    /**
     * SQLiteProvider constructor.
     *
     * @param Crates $plugin
     */
    public function __construct(Crates $plugin) {
        $this->plugin = $plugin;
        $this->database = new SQLite3($plugin->getDataFolder() . "Players.db");
        $this->database->exec("CREATE TABLE IF NOT EXISTS players(uuid VARCHAR(36), username VARCHAR(16), crates TEXT DEFAULT '' NOT NULL);");
    }

    /**
     * @return SQLite3
     */
    public function getDatabase(): SQLite3 {
        return $this->database;
    }

    /**
     * @param Player $player
     *
     * @return int[]
     */
    public function getCrateKeys(Player $player): array {
        $uuid = $player->getRawUniqueId();
        $stmt = $this->database->prepare("SELECT crates FROM players WHERE uuid = :uuid;");
        $stmt->bindValue(":uuid", $uuid);
        $result = $stmt->execute();
        $crates = $result->fetchArray(SQLITE3_ASSOC)["crates"];
        return $this->plugin->decodeKeys($crates);
    }

    /**
     * @param Player $player
     *
     * @return bool
     */
    public function isRegistered(Player $player): bool {
        $uuid = $player->getRawUniqueId();
        $stmt = $this->database->prepare("SELECT username FROM players WHERE uuid = :uuid;");
        $stmt->bindValue(":uuid", $uuid);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC)["username"] !== null ? true : false;

    }

    /**
     * @param Player $player
     */
    public function register(Player $player) {
        $uuid = $player->getRawUniqueId();
        $username = $player->getName();
        $stmt = $this->database->prepare("INSERT INTO players(uuid, username) VALUES(:uuid, :username);");
        $stmt->bindValue(":uuid", $uuid);
        $stmt->bindValue(":username", $username);
        $stmt->execute();
        $this->plugin->getLogger()->notice("Registering {$player->getName()} into the quickCrates database!");
    }

    /**
     * @param Player $player
     */
    public function setCrateKeys(Player $player) {
        $uuid = $player->getRawUniqueId();
        $session = $this->plugin->getSessionManager()->getSession($player);
        $crates = $this->plugin->encodeKeys($session->getKeys());
        $stmt = $this->database->prepare("UPDATE players SET crates = :crates WHERE uuid = :uuid;");
        $stmt->bindValue(":crates", $crates);
        $stmt->bindValue(":uuid", $uuid);
        $stmt->execute();
        return;
    }
}