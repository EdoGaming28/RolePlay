<?php

namespace FazrilDev\Task;

use FazrilDev\Main;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Utils;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;

class WheaterTask extends AsyncTask
{
	
    public function onRun()
    {
        $url = Utils::getUrl("https://rest.farzain.com/api/cuaca.php?id=Jakarta&apikey=O8mUD3YrHIy9KM1fMRjamw8eg");
        $data = json_decode($url, true);
        $this->setResult(['status' => $data['status'], 'list' => $data]);
    }

    public function onCompletion(Server $server)
    {
        if($this->getResult()['status'] === 400){
            Main::getInstance()->getScheduler()->cancelTask($this->getTaskId());
        }
            foreach ($server->getOnlinePlayers() as $player) {
                if ((string)$this->getResult()['list']['respon']['cuaca'] === "Rain") {
                    Main::getInstance()->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender, "wrain");
                    $server->getLogger()->info("rain");
                }else{
                	Main::getInstance()->getServer()->getCommandMap()->dispatch(new ConsoleCommandSender, "wclear");
                    $server->getLogger()->info("clouds");
                }
            }
        }

        public function getPlugin(): Main{
        	return Main::getInstance();
        }
}