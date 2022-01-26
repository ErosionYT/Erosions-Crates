<?php

declare(strict_types = 1);

namespace ErosionYT\Crates\command;

use ErosionYT\Crates\command\types\KeyCommand;
use ErosionYT\Crates\command\types\KeyAllCommand;
use ErosionYT\Crates\Crates;
use pocketmine\command\Command;
use pocketmine\plugin\PluginException;

class CommandManager {

    /** @var Crates */
    private $plugin;

    /**
     * CommandManager constructor.
     *
     * @param Crates $plugin
     */
    public function __construct(Crates $plugin) {
        $this->plugin = $plugin;
        $this->registerCommand(new KeyCommand());
        $this->registerCommand(new KeyAllCommand());
    }

    /**
     * @param Command $command
     */
    public function registerCommand(Command $command): void {
        $commandMap = $this->plugin->getServer()->getCommandMap();
        $existingCommand = $commandMap->getCommand($command->getName());
        if($existingCommand !== null) {
            $commandMap->unregister($existingCommand);
        }
        $commandMap->register($command->getName(), $command);
    }

    /**
     * @param string $name
     */
    public function unregisterCommand(string $name): void {
        $commandMap = $this->plugin->getServer()->getCommandMap();
        $command = $commandMap->getCommand($name);
        if($command === null) {
            throw new PluginException("Invalid command: $name to un-register.");
        }
        $commandMap->unregister($commandMap->getCommand($name));
    }
}