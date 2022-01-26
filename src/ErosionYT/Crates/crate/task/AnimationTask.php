<?php

declare(strict_types = 1);

namespace ErosionYT\Crates\crate\task;

use ErosionYT\Crates\crate\Crate;
use ErosionYT\Crates\crate\Reward;
use ErosionYT\Crates\Crates;
use pocketmine\entity\Entity;
use pocketmine\level\particle\FlameParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class AnimationTask extends Task {

    /** @var int */
    private $runs = 0;

    /** @var Crate */
    private $crate;

    /** @var Player */
    private $player;

    /** @var int */
    private $id;

    /**
     * AnimationTask constructor.
     *
     * @param Crate $crate
     * @param Player $player
     */
    public function __construct(Crate $crate, Player $player) {
        $this->crate = $crate;
        $session = Crates::getInstance()->getSessionManager()->getSession($player);
        $session->setCrateRunning();
        $this->player = $player;
    }

    /**
     * @param Reward $reward
     */
    public function spawnItemEntity(Reward $reward) {
        $this->id = Entity::$entityCount++;
        $pk = new AddItemActorPacket();
        $pk->item = $reward->getItem();
        $pk->position = $this->crate->getPosition()->add(0.5, 0.75, 0.5);
        $pk->entityRuntimeId = $this->id;
        $this->player->dataPacket($pk);
    }

    public function removeItemEntity() {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->id;
        $this->player->dataPacket($pk);
    }

    /**
     * @param int $currentTick
     */
    public function onRun(int $currentTick) {
        if($this->player->isClosed()) {
            Crates::getInstance()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }
        ++$this->runs;
        $position = $this->crate->getPosition();
        if($this->runs === 1) {
            $pk = new LevelSoundEventPacket();
            $pk->position = $position;
            $pk->sound = LevelSoundEventPacket::SOUND_CHEST_OPEN;
            $this->player->sendDataPacket($pk);
            $pk = new BlockEventPacket();
            $pk->x = $position->getFloorX();
            $pk->y = $position->getFloorY();
            $pk->z = $position->getFloorZ();
            $pk->eventType = 1;
            $pk->eventData = 1;
            $this->player->sendDataPacket($pk);
            return;
        }
        if($this->runs === 4) {
            $cx = $position->getX() + 0.5;
            $cy = $position->getY() + 1.2;
            $cz = $position->getZ() + 0.5;
            $radius = 1;
            for($i = 0; $i < 50; $i += 1.1){
                $x = $cx + ($radius * cos($i));
                $z = $cz + ($radius * sin($i));
                $pos = new Vector3($x, $cy, $z);
                $position->level->addParticle(new FlameParticle($pos), [$this->player]);
            }
            $reward = $this->crate->getReward();
            $callable = $reward->getCallback();
            $callable($this->player);
            $xp = $this->player->getCurrentTotalXp();
            $this->player->addXpLevels(20, true);
            $this->player->setCurrentTotalXp($xp);
            $this->spawnItemEntity($reward);
            $this->crate->showReward($reward, $this->player);
            return;
        }
        if($this->runs === 7) {
            $pk = new LevelSoundEventPacket();
            $pk->position = $position;
            $pk->sound = LevelSoundEventPacket::SOUND_CHEST_CLOSED;
            $this->player->sendDataPacket($pk);
            $pk = new BlockEventPacket();
            $pk->x = $position->getFloorX();
            $pk->y = $position->getFloorY();
            $pk->z = $position->getFloorZ();
            $pk->eventType = 1;
            $pk->eventData = 0;
            $this->player->sendDataPacket($pk);
            $this->removeItemEntity();
            $session = Crates::getInstance()->getSessionManager()->getSession($this->player);
            $this->crate->updateTo($this->player);
            $session->setCrateRunning(false);
            Crates::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}