<?php

namespace FazrilDev\Task;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\item\{Item, ItemIds, ItemFactory, ItemBlock};
use FazrilDev\Main;
use pocketmine\scheduler\Task;

class setMaxHealth extends Task {

    public $timer = 120;
    
    public function __construct(Main $main, $playerName)
    {
        $this->main = $main;
        $this->playerName = $playerName;
    }
    
    public function onRun(int $currentTick){
    	$player = $this->main->getServer()->getPlayerExact($this->playerName);
        if($player instanceof Player){
        	$health = $player->getMaxHealth() + 2;
        	$this->timer--;
            $player->addSubTitle("§6Effect Cake: §e{$this->timer}s");
            if($this->timer === 119){
            	$player->setMaxHealth($player->getMaxHealth() + 2);
            }
            if($this->timer === 0){
            	$player->setMaxHealth($player->getMaxHealth() - 2);
                $this->main->getScheduler()->cancelTask($this->getTaskId());
            }
        }else{
        	$this->main->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}