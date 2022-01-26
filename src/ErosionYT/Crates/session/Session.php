<?php

namespace ErosionYT\Crates\session;

use ErosionYT\Crates\crate\Crate;
use ErosionYT\Crates\Crates;
use ErosionYT\Crates\utils\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\plugin\PluginException;

class Session {

    /** @var Player */
    private $owner;

    /** @var int[] */
    private $keys = [];

    /** @var FloatingTextParticle[] */
    private $particles = [];

    /** @var bool */
    private $crateRunning = false;

    /**
     * Session constructor.
     *
     * @param Player $player
     */
    public function __construct(Player $player) {
        $this->owner = $player;
        $provider = Crates::getInstance()->getProvider();
        $this->keys = $provider->getCrateKeys($player);
        foreach(Crates::getInstance()->getCratesManager()->getCrates() as $crate) {
            if(!isset($this->keys[$crate->getIdentifier()])) {
                $this->keys[$crate->getIdentifier()] = 0;
            }
        }
    }

    /**
     * @return Player
     */
    public function getOwner(): Player {
        return $this->owner;
    }

    /**
     * @return int[]
     */
    public function getKeys(): array {
        return $this->keys;
    }

    /**
     * @param Crate $crate
     *
     * @return int
     */
    public function getKeysByCrate(Crate $crate): int {
        return $this->keys[$crate->getIdentifier()];
    }

    /**
     * @param string $identifier
     * @param int $amount
     */
    public function addKeys(string $identifier, int $amount): void {
        $this->keys[$identifier] = max($this->keys[$identifier] + $amount, 0);
        Crates::getInstance()->getProvider()->setCrateKeys($this->owner);
    }

    /**
     * @param string $identifier
     * @param int $amount
     */
    public function subtractKeys(string $identifier, int $amount): void {
        $this->keys[$identifier] = max($this->keys[$identifier] - $amount, 0);
        Crates::getInstance()->getProvider()->setCrateKeys($this->owner);
    }

    /**
     * @return FloatingTextParticle[]
     */
    public function getFloatingTexts(): array {
        return $this->particles;
    }

    /**
     * @param string $identifier
     *
     * @return FloatingTextParticle|null
     */
    public function getFloatingText(string $identifier): ?FloatingTextParticle {
        return $this->particles[$identifier] ?? null;
    }

    /**
     * @param Position $position
     * @param string $identifier
     * @param string $message
     *
     * @throws PluginException
     */
    public function addFloatingText(Position $position, string $identifier, string $message): void {
        if($position->getLevel() === null) {
            throw new PluginException("Attempt to add a floating text particle with an invalid level.");
        }
        $floatingText = new FloatingTextParticle($position, $identifier, $message);
        $this->particles[$identifier] = $floatingText;
        $floatingText->sendChangesTo($this->owner);
    }

    /**
     * @param string $identifier
     *
     * @throws PluginException
     */
    public function removeFloatingText(string $identifier): void {
        $floatingText = $this->getFloatingText($identifier);
        if($floatingText === null) {
            throw new PluginException("Failed to despawn floating text: $identifier");
        }
        $floatingText->despawn($this->owner);
        unset($this->particles[$identifier]);
    }


    /**
     * @return bool
     */
    public function isCrateRunning(): bool {
        return $this->crateRunning;
    }

    /**
     * @param bool $value
     */
    public function setCrateRunning(bool $value = true): void {
        $this->crateRunning = $value;
    }
}