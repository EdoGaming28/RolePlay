<?php

namespace FazrilDev\Task;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\item\{Item, ItemIds, ItemFactory, ItemBlock};
use FazrilDev\Main;
use pocketmine\scheduler\Task;

class MessageTask extends Task {

    public $timer = 10;
    
    public function __construct(Main $main, $playerName)
    {
   
        $this->main = $main;
        $this->playerName = $playerName;
    }
    
    public function onRun(int $currentTick){
    	$player = $this->main->getServer()->getPlayerExact($this->playerName);
        if($player instanceof Player){
        	$this->timer--;
            if($this->timer === 8){
            	$player->sendMessage("§6[1/4] §ePak Sunardi: §fAssalamuallaikum ".$this->playerName.", §fkamu sudah mengambil tugas dari bapak??");
            }
            if($this->timer === 6){
            	$player->sendMessage("§6[2/4] §e".$this->playerName."§e: §fWaallaikumsalam, belum pak :(");
            }
            if($this->timer === 4){
            	$player->sendMessage("§6[3/4] §ePak Sunardi: §fSegera di ambil ya ".$this->playerName);
            }
            if($this->timer === 2){
            	$player->sendMessage("§6[4/4] §e".$this->playerName."§e: §fBaik pak, hehe maaf ya. KABOORRR!!!");
            }
            if($this->timer === 0){
            	$this->main->getScheduler()->cancelTask($this->getTaskId());
            }
        }else{
        	$this->main->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}