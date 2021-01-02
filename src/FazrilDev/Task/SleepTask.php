<?php

namespace FazrilDev\Task;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\item\{Item, ItemIds, ItemFactory, ItemBlock};
use FazrilDev\Main;
use pocketmine\scheduler\Task;

class SleepTask extends Task {

    public $timer = 10;
    
    public function __construct(Main $main, Player $player)
    {
    	$this->main = $main;
        $this->player = $player;
    }
    
    public function onRun(int $currentTick){
    	$sender = $this->player;
        $this->timer--;
        if($this->timer === 9){
        	$this->main->getServer()->broadcastMessage("§f{$sender->getName()} Start to sleep §f>>> §l§c[§eCancel§c]");
        }
        if($this->timer === mt_rand(1, 8)){
        	$this->main->getServer()->broadcastMessage("§fSleep cancelling by §l§eCONSOLE");
            $sender->stopSleep();
        }
    }
}