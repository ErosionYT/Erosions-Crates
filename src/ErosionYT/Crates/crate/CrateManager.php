<?php

declare(strict_types = 1);

namespace ErosionYT\Crates\crate;

use ErosionYT\Crates\Crates;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class CrateManager {

    /** @var Crates */
    private $plugin;

    /** @var Crate[] */
    private $crates = [];

    /**
     * CrateManager constructor.
     *
     * @param Crates $plugin
     */
    public function __construct(Crates $plugin) {
        $this->plugin = $plugin;
        $this->init();
    }

    public function init() {
        foreach(scandir($path = $this->plugin->getDataFolder() . "crates" . DIRECTORY_SEPARATOR) as $file) {
            $parts = explode(".", $file);
            if($file == "." or $file == "..") {
                continue;
            }
            if(is_file($path . $file) and isset($parts[1]) and $parts[1] == "yml") {
                $config = new Config($path . $file);
                $rewards = [];
                foreach($config->get("rewards") as $reward) {
                    $parts = explode("/", $reward);
                    $name = TextFormat::colorize($parts[0]);
                    $displayItem = Item::get((int)$parts[1], 0, 1);
                    $chance = (int)$parts[2];
                    $type = (string)$parts[3];
                    if($type === "command") {
                        $command = (string)$parts[4];
                        $rewards[] = new CommandReward($name, $displayItem, $command, $chance);
                        continue;
                    }
                    if($type === "item") {
                        $item = Item::get((int)$parts[5], (int)$parts[6], (int)$parts[7]);
                        $customName = (string)$parts[4];
                        if($customName !== "default") {
                            $item->setCustomName(TextFormat::colorize($customName));
                        }
                        $enchantments = array_slice($parts, 8);
                        if(!empty($enchantments)) {
                            $enchantmentsArrays = array_chunk($enchantments, 2);
                            foreach($enchantmentsArrays as $enchantmentsData) {
                                if(count($enchantmentsData) !== 2) {
                                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Had trouble parsing this part: $reward Please check for errors");
                                    return;
                                }
                                $enchantmentId = (int)$enchantmentsData[0];
                                $enchantment = Enchantment::getEnchantment($enchantmentId);
                                $enchantmentLevel = (int)$enchantmentsData[1];
                                $enchantment = new EnchantmentInstance($enchantment, $enchantmentLevel);
                                $item->addEnchantment($enchantment);
                            }
                        }
                        $rewards[] = new ItemReward($name, $displayItem, $item, $chance);
                        continue;
                    }
                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Had trouble parsing this part: $reward Please check for errors");
                    return;
                }
                $name = (string)$config->get("name", "Undefined");
                $displayName = (string)$config->get("displayName", "Undefined");
                $identifier = (string)$config->get("identifier", "Undefined");
                if(!preg_match("/[a-z]/i", $identifier)) {
                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Identifier can only contain letters!");
                    return;
                }
                if(strpos($identifier, " ") !== false) {
                    $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file. Identifier can't contain spaces!");
                }
                $position = (string)$config->get("position", "Undefined");
                $position = explode(":", $position);
                $position = new Position((int)$position[0], (int)$position[1], (int)$position[2], $this->plugin->getServer()->getWorldManager()->getWorldByName((string)$position[3]));
                $crate = new Crate($name, $displayName, $identifier, $position, $rewards);
                $this->addCrate($crate);
            }
            else {
                $this->plugin->getLogger()->error("Error while parsing {$file} as crate because it is not a valid YAML file");
            }
        }
    }

    /**
     * @return Crate[]
     */
    public function getCrates(): array {
        return $this->crates;
    }

    /**
     * @param string $identifier
     *
     * @return Crate|null
     */
    public function getCrate(string $identifier): ?Crate {
        return isset($this->crates[strtolower($identifier)]) ? $this->crates[strtolower($identifier)] : null;
    }

    /**
     * @param Crate $crate
     */
    public function addCrate(Crate $crate) {
        $this->crates[strtolower($crate->getIdentifier())] = $crate;
    }
}