<?php

namespace FazrilDev\Task;

use FazrilDev\Main;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;
use pocketmine\Server;

class DayTask extends AsyncTask {
    
    public function onRun() {
		@date_default_timezone_set("Asia/Jakarta");
	}
	
	public function onCompletion(Server $server){
		if(is_null($server->getDefaultLevel())){
			Main::getInstance()->getServer()->getPluginManager()->disablePlugin(Main::getInstance()->getServer()->getPluginManager()->getPlugin("RolePlay"));
		}
		
		$jam = date("g");
		$data = date("A");
		$total = date("g:i");
		if($data === "AM"){
			if($jam == 1){
				$server->getDefaultLevel()->setTime(19000);
			}
			if($jam == 2){
				$server->getDefaultLevel()->setTime(20000);
			}
			if($jam == 3){
				$server->getDefaultLevel()->setTime(21000);
			}
			if($jam == 4){
				$server->getDefaultLevel()->setTime(22000);
			}
			if($jam == 5){
				$server->getDefaultLevel()->setTime(23000);
			}
			if($jam == 6){
				$server->getDefaultLevel()->setTime(0);
			}
			if($jam == 7){
				$server->getDefaultLevel()->setTime(1000);
			}
			if($jam == 8){
				$server->getDefaultLevel()->setTime(2000);
			}
			if($jam == 9){
				$server->getDefaultLevel()->setTime(3000);
			}
			if($jam == 10){
				$server->getDefaultLevel()->setTime(4000);
			}
			if($jam == 11){
				$server->getDefaultLevel()->setTime(5000);
			}
			if($jam == 12){
				$server->getDefaultLevel()->setTime(18000);
			}
		}else if($data === "PM"){
			if($jam == 1){
				$server->getDefaultLevel()->setTime(7000);
			}
			if($jam == 2){
				$server->getDefaultLevel()->setTime(8000);
			}
			if($jam == 3){
				$server->getDefaultLevel()->setTime(9000);
			}
			if($jam == 4){
				$server->getDefaultLevel()->setTime(10000);
			}
			if($jam == 5){
				$server->getDefaultLevel()->setTime(11000);
			}
			if($jam == 6){
				$server->getDefaultLevel()->setTime(12000);
			}
			if($jam == 7){
				$server->getDefaultLevel()->setTime(13000);
			}
			if($jam == 8){
				$server->getDefaultLevel()->setTime(14000);
			}
			if($jam == 9){
				$server->getDefaultLevel()->setTime(15000);
			}
			if($jam == 10){
				$server->getDefaultLevel()->setTime(16000);
			}
			if($jam == 11){
				$server->getDefaultLevel()->setTime(17000);
			}
			if($jam == 12){
				$server->getDefaultLevel()->setTime(6000);
			}
		}
	}
}