<?php

declare(strict_types = 1);

namespace ErosionYT\Crates\command\types;

use ErosionYT\Crates\Crates;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class KeyCommand extends Command {

    /**
     * GiveKeysCommand constructor.
     */
    public function __construct() {
        parent::__construct("key", "Give crate keys to a player.", "/key <player> <crate> [amount = 1]");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->isOp()) {
            if(!isset($args[2])) {
                $sender->sendMessage(TextFormat::YELLOW . $this->getUsage());
                return;
            }
            $player = Crates::getInstance()->getServer()->getPlayer($args[0]);
            if(!$player instanceof Player) {
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "INVALID PLAYER!");
                return;
            }
            $crate = Crates::getInstance()->getCratesManager()->getCrate($args[1]);
            if($crate === null) {
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "INVALID CRATE!");
                return;
            }
            $amount = max(1, is_numeric($args[2]) ? (int)$args[2] : 1);
            $session = Crates::getInstance()->getSessionManager()->getSession($player);
            $session->addKeys($crate->getIdentifier(), $amount);
            return;
        }
        $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "NO PERMISSION!");
        return;
    }
}