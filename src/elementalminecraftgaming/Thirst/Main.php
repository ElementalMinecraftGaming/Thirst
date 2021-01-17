<?php

namespace elementalminecraftgaming\Thirst;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\event\entity\effect\EntityEffectEvent;
use pocketmine\entity\effect\Effect;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\CommandSender;
use pocketmine\item\Potion;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\command\ConsoleCommandSender;
use leet\Thirst\waterInterval;
use leet\Thirst\dehydrateInterval;
use pocketmine\command\Command;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener {

    public $db;
    public $Interval;
    public $dInterval;
    public $plugin;
    public $plug;

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->db = new \SQLite3($this->getDataFolder() . "Thirst.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS Player(user TEXT PRIMARY KEY, water INT);");
        $this->Interval = new Config($this->getDataFolder() . "waterInterval.yml", Config::YAML, array("Interval" => 30));
        $this->getScheduler()->scheduleRepeatingTask(new waterInterval($this), $this->Interval->get("Interval") * 20);
        $this->dInterval = new Config($this->getDataFolder() . "dehydrateInterval.yml", Config::YAML, array("Interval" => 5));
        $this->getScheduler()->scheduleRepeatingTask(new dehydrateInterval($this), $this->dInterval->get("Interval") * 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function Msg($string) {
        return TextFormat::BOLD . TextFormat::DARK_AQUA . "[" . TextFormat::AQUA . "Thirst" . TextFormat::DARK_AQUA . "] " . TextFormat::GREEN . "$string";
    }

    public function Newn($name) {
        $del = $this->db->prepare("INSERT OR REPLACE INTO Player (user, water) VALUES (:user, :water);");
        $water = 20;
        $del->bindValue(":user", $name);
        $del->bindValue(":water", $water);
        $start = $del->execute();
    }

    public function dehydrate() {
        $players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $player) {
            $name = $this->getName($player);
            $del = $this->db->prepare("INSERT OR REPLACE INTO Player (user, water) VALUES (:user, :water);");
            $water = $this->Newn($name);
            if ($water > 0) {
                $newwater = $water - 1;
                $del->bindValue(":user", $name);
                $del->bindValue(":water", $newwater);
                $start = $del->execute();
            } else {
                $this->dehydration();
            }
        }
    }

    public function onDigest(PlayerItemConsumeEvent $event) {
        $players = $event->getPlayer();
        $name = $event->getName($player);
        $item = $event->getItem();
        $del = $this->db->prepare("INSERT OR REPLACE INTO Player (user, water) VALUES (:user, :water);");
        $water = $this->New($name);
        if ($water <= 19) {
            if ($item->getId() == 0) {
                $newwater = $water + 1;
                $del->bindValue(":user", $name);
                $del->bindValue(":water", $newwater);
                $start = $del->execute();
            }
        }
    }

    public function dehydration() {
        $players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $player) {
            $name = $this->getName($player);
            $water = $this->Newn($name);
            if ($water <= 0) {
                $health = $player->getHealth();
                $player->setHealth($health - 2);
                $player->addEffect(Effect::getEffect(2)->setAmplifier(1)->setDuration(20 * 2)->setVisible(false));
                $player->addEffect(Effect::getEffect(18)->setAmplifier(1)->setDuration(20 * 2)->setVisible(false));
            }
        }
    }

    public function userKnown($user) {
        $username = \SQLite3::escapeString($user);
        $search = $this->db->prepare("SELECT * FROM Player WHERE user = :user;");
        $search->bindValue(":user", $username);
        $start = $search->execute();
        $delta = $start->fetchArray(SQLITE3_ASSOC);
        return empty($delta) == false;
    }

    public function getWater($name) {
        $search = $this->db->prepare("SELECT water FROM Player WHERE user = :user;");
        $search->bindValue(":user", $name);
        $start = $search->execute();
        $da = $start->fetchArray(SQLITE3_ASSOC);
        return (INT) $da["water"];
    }

    public function join(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $event->getPlayer()->getName();
        if ($this->userKnown($name) == false) {
            $this->Newn($name);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (strtolower($command->getName()) == "waterlevel") {
            if ($sender->hasPermission("thirst.player")) {
                if ($sender instanceof Player) {
                    if (isset($args[0])) {
                        $user = $sender->getName();
                        $name = $args[0];
                        $checkname = $this->userKnown($name);
                        if ($checkname == true) {
                            $water = $this->getWater($name);
                            $sender->sendMessage($this->Msg("This player's water level is: $water!"));
                            return true;
                        } else {
                            $sender->sendMessage($this->Msg("This player has never joined this server!"));
                        }
                    } else {
                        $water = $this->getWater($user);
                        $sender->sendMessage($this->Msg("Your water level is: $water!"));
                    }
                } else {
                    $sender->sendMessage($this->Msg("Please run this command in-game!"));
                }
            } else {
                $sender->sendMessage($this->Msg("No Permissions!"));
                return false;
            }
        }
    }

}
