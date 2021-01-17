<?php

namespace leet\Thirst;

use pocketmine\scheduler\Task;
use leet\Thirst\Main;

class waterInterval extends Task {

    public $plug;

    public function __construct(Main $plg) {
        $this->plugin = $plg;
    }

    public function onRun(int $currentTick) {
        $this->plugin->dehydration();
    }

}
