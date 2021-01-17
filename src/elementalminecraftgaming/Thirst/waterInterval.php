<?php

namespace leet\Thirst;

use pocketmine\scheduler\Task;
use leet\Thirst\Main;

class waterInterval extends Task {

    public $plugin;

    public function __construct(Main $pg) {
        $this->plugin = $pg;
    }

    public function onRun(int $currentTick) {
        $this->plugin->dehydrate(1);
    }

}
