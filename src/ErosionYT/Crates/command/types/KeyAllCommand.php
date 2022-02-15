<?php

declare(strict_types = 1);

namespace ErosionYT\Crates\command\types;

use ErosionYT\Crates\Crates;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class KeyAllCommand extends Command {

    /**
     * KeyAllCommand constructor.
     */
    public function __construct() {
        parent::__construct("keyall", "Give crate keys to all players.", "/keyall <crate> <amount>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if($sender instanceof ConsoleCommandSender or $sender->isOp()) {
            if(!isset($args[1])) {
                $sender->sendMessage(TextFormat::YELLOW . $this->getUsage());
                return;
            }
            $crate = Crates::getInstance()->getCratesManager()->getCrate($args[0]);
            if($crate === null) {
                $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "INVALID CRATE!");
                return;
            }

            $amount = is_numeric($args[1]) ? (int)$args[1] : 1;

            /** @var Player $player */
            foreach(Crates::getInstance()->getServer()->getOnlinePlayers() as $player) {
                $session = Crates::getInstance()->getSessionManager()->getSession($player);
                $session->addKeys($crate->getIdentifier(), $amount);
                $crate->updateTo($player);
            }
            $message = TextFormat::colorize((string)Crates::getInstance()->getConfig()->get("keyall"));
            $message = str_replace("{name}", $sender->getName(), $message);
            $message = str_replace("{amount}", $amount, $message);
            $message = str_replace("{crate}", $crate->getDisplayName(), $message);
            Crates::getInstance()->getServer()->broadcastMessage($message);
            return;
        }
        $sender->sendMessage(TextFormat::DARK_RED . TextFormat::BOLD . "NO PERMISSION!");
        return;
    }
}