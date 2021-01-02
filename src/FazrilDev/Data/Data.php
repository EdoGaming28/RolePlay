<?php

namespace FazrilDev\Data;

use pocketmine\scheduler\Task;
use FazrilDev\Main;
use FazrilDev\Task\WheaterTask;

class Data extends Task{
    
    public function onRun(int $currentTick){
    	Main::getInstance()->getServer()->getAsyncPool()->submitTask(new WheaterTask());
    }
}