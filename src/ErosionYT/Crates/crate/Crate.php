<?php

declare(strict_types = 1);

namespace ErosionYT\Crates\crate;

use ErosionYT\Crates\Crates;
use ErosionYT\Crates\crate\task\AnimationTask;
use pocketmine\level\Position;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Crate {

    /** @var string */
    private $name;

    /** @var string */
    private $displayName;

    /** @var string */
    private $identifier;

    /** @var Position */
    private $position;

    /** @var Reward[] */
    private $rewards = [];

    /**
     * Crate constructor.
     *
     * @param string $name
     * @param string $displayName
     * @param string $identifier
     * @param Position $position
     * @param array $rewards
     */
    public function __construct(string $name, string $displayName, string $identifier, Position $position, array $rewards) {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->identifier = $identifier;
        $this->position = $position;
        $this->rewards = $rewards;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player): void {
        $session = Crates::getInstance()->getSessionManager()->getSession($player);
        $particle = $session->getFloatingText($this->identifier);
        if($particle !== null) {
            return;
        }
        $session->addFloatingText(Position::fromObject($this->getPosition()->add(0.5, 1.25, 0.5), $this->getPosition()->getWorld()), $this->identifier, $this->getDisplayName() . " Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::AQUA . $session->getKeysByCrate($this) . TextFormat::WHITE . " keys");
    }

    /**
     * @param Player $player
     */
    public function updateTo(Player $player): void {
        $session = Crates::getInstance()->getSessionManager()->getSession($player);
        $particle = $session->getFloatingText($this->identifier);
        if($particle === null) {
            $this->spawnTo($player);
            return;
        }
        $particle->update($this->getDisplayName() . " Crate\n" . TextFormat::RESET . TextFormat::WHITE . "You have " . TextFormat::AQUA . $session->getKeysByCrate($this) . TextFormat::WHITE . " keys");
        $particle->sendChangesTo($player);
    }

    /**
     * @param Player $player
     */
    public function despawnTo(Player $player): void {
        $session = Crates::getInstance()->getSessionManager()->getSession($player);
        $particle = $session->getFloatingText($this->identifier);
        if($particle !== null) {
            $particle->despawn($player);
        }
    }

    /**
     * @param Reward $reward
     * @param Player $player
     */
    public function showReward(Reward $reward, Player $player): void {
        $session = Crates::getInstance()->getSessionManager()->getSession($player);
        $particle = $session->getFloatingText($this->identifier);
        if($particle === null) {
            $this->spawnTo($player);
            return;
        }
        $particle->update(TextFormat::BOLD . TextFormat::AQUA . $reward->getName());
        $particle->sendChangesTo($player);
    }

    /**
     * @param Player $player
     */
    public function try(Player $player): void {
        $session = Crates::getInstance()->getSessionManager()->getSession($player);
        if($session->isCrateRunning() === true) {
            $player->sendMessage(TextFormat::colorize((string)Crates::getInstance()->getConfig()->get("animationAlreadyRunning")));
            $player->knockBack($player, 0, $player->getX() - $this->position->getX(), $player->getZ() - $this->position->getZ(), 1);
            return;
        }
        if($player->getInventory()->getSize() === count($player->getInventory()->getContents())) {
            $player->sendMessage(TextFormat::colorize((string)Crates::getInstance()->getConfig()->get("fullInventory")));
            $player->knockBack($player, 0, $player->getX() - $this->position->getX(), $player->getZ() - $this->position->getZ(), 1);
            return;
        }
        if($session->getKeysByCrate($this) <= 0) {
            $player->sendMessage(TextFormat::colorize((string)Crates::getInstance()->getConfig()->get("noKeys")));
            $player->knockBack($player, 0, $player->getX() - $this->position->getX(), $player->getZ() - $this->position->getZ(), 1);
            return;
        }
        $session->subtractKeys($this->identifier, 1);
        Crates::getInstance()->getScheduler()->scheduleRepeatingTask(new AnimationTask($this, $player), 5);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string {
        return TextFormat::colorize($this->displayName);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string {
        return $this->identifier;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position {
        return $this->position;
    }

    /**
     * @return Reward[]
     */
    public function getRewards(): array {
        return $this->rewards;
    }

    /**
     * @param int $loop
     *
     * @return Reward
     */
    public function getReward(int $loop = 0): Reward {
        $chance = mt_rand(0, 100);
        $reward = $this->rewards[array_rand($this->rewards)];
        if($loop >= 10) {
            return $reward;
        }
        if($reward->getChance() <= $chance) {
            return $this->getReward($loop + 1);
        }
        return $reward;
    }
}