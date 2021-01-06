<?php

namespace FazrilDev;

 use _64FF00\PurePerms\PurePerms;
 use onebone\economyapi\EconomyAPI;

 //menu
 use libs\muqsit\invmenu\InvMenu;
 use libs\muqsit\invmenu\InvMenuHandler;
 use libs\form\FormAPI;
 use libs\form\CustomForm;
 use libs\form\SimpleForm;
 use libs\form\Form;
 
 //Sound
 use FazrilDev\Sound\{
     QuestOpenSound,
     SoundQuestDone,
     SoundSuccess,
     SoundNoMoney
 };
 //pos
 use pocketmine\level\Position;
 use pocketmine\math\Vector3;
 
 //entity
 use revivalpmmp\pureentities\entity\monster\walking\Zombie;
 use revivalpmmp\pureentities\entity\monster\walking\Skeleton;
 use revivalpmmp\pureentities\entity\animal\walking\Cow;
 use revivalpmmp\pureentities\entity\animal\walking\Sheep;
 use revivalpmmp\pureentities\entity\animal\walking\Chicken;
 use pocketmine\entity\Entity;
 use pocketmine\entity\object\Painting;
 
 //recipe
 use pocketmine\inventory\ShapedRecipe;
 use pocketmine\inventory\ShapelessRecipe;
 
 use pocketmine\entity\EntityEffectAddEvent;
 use pocketmine\entity\EntityEffectEvent;
 use pocketmine\entity\Effect;
 use pocketmine\entity\EffectInstance;
 
 use pocketmine\Server;
 use pocketmine\plugin\PluginBase;
 use pocketmine\Player;
 use FazrilDev\Task\{MessageTask, CompleteTask, DayTask, WeatherTask, SleepTask, setMaxHealth};
 use FazrilDev\Data\Data;
 use pocketmine\scheduler\ClosureTask;
 use pocketmine\item\ItemBlock;
 use pocketmine\event\Listener;
 use pocketmine\scheduler\Task;
 use pocketmine\event\player\PlayerJoinEvent;
 use pocketmine\event\player\PlayerInteractEvent;
 use pocketmine\event\player\PlayerBedEnterEvent;
 use pocketmine\event\player\PlayerItemConsumeEvent;
 use pocketmine\event\player\PlayerPickUpItemEvent;
 use pocketmine\event\player\PlayerCommandPreprocessEvent;
 use pocketmine\event\player\PlayerChatEvent;
 use pocketmine\event\entity\EntityLevelChangeEvent;
 use pocketmine\event\inventory\CraftItemEvent;
 use pocketmine\event\entity\EntityDamageEvent;
 use pocketmine\event\entity\EntityDamageByEntityEvent;
 use pocketmine\event\inventory\InventoryPickupItemEvent;
 
 use pocketmine\command\Command;
 use pocketmine\command\CommandSender;
 use pocketmine\command\SimpleCommandMap;
 use pocketmine\command\ConsoleCommandSender;
 
 use pocketmine\item\Item;
 use pocketmine\item\NetheriteHelmet;
 use pocketmine\item\NetheriteChestplate;
 use pocketmine\item\NetheriteLeggings;
 use pocketmine\block\CraftingTable;
 use pocketmine\item\Book;
 use pocketmine\block\Bookshelf;
 use pocketmine\block\EnchantingTable;
 use pocketmine\item\NetheriteBoots;
 use pocketmine\item\Cake;
 use pocketmine\item\Stick;
 use pocketmine\block\Wood;
 use pocketmine\block\Block;
 
 use pocketmine\item\ItemFactory;
 use pocketmine\item\enchantment\Enchantment;
 use pocketmine\item\enchantment\EnchantmentInstance;
 use pocketmine\event\block\BlockPlaceEvent;
 use pocketmine\event\block\BlockBreakEvent;
 //nbt
 use pocketmine\nbt\tag\StringTag;
 use pocketmine\nbt\tag\CompoundTag;

 use pocketmine\level\sound\{PopSound, ClickSound, EndermanTeleportSound, Sound, BlazeShootSound};
 use pocketmine\level\particle\DestroyBlockParticle;
 
 use pocketmine\utils\Config;
 use Scoreboards\Scoreboards;
 use pocketmine\level\particle\{DustParticle, FlameParticle, FloatingTextParticle, EntityFlameParticle, CriticalParticle, ExplodeParticle, HeartParticle, LavaParticle, MobSpawnParticle, SplashParticle};
 
 use pocketmine\network\mcpe\protocol\AddActorPacket;
 use pocketmine\network\mcpe\protocol\LevelEventPacket;
 use pocketmine\network\mcpe\protocol\PlaySoundPacket;
 
class Main extends PluginBase implements Listener {
    
    private static $instance;
    public $timer;
    public $weather;
    
	public function onEnable(){
		$this->timer = 1;
		$this->weather = mt_rand(0,1);
		@date_default_timezone_set("Asia/Jakarta");
		/*$this->kelas = new Config($this->getDataFolder()."kelas.yml", Config::YAML, array());*/
		$this->Task1 = new Config($this->getDataFolder() . "task1.yml", Config::YAML, array());
		$this->Task2 = new Config($this->getDataFolder() . "task2.yml", Config::YAML, array());
		$this->Task3 = new Config($this->getDataFolder() . "task3.yml", Config::YAML, array());
		$this->Task4 = new Config($this->getDataFolder() . "task4.yml", Config::YAML, array());
		/*$this->uangJajan = new Config($this->getDataFolder() . "uang.yml", Config::YAML, array());*/
		/*if(!$this->kelas->exists("member")){
			$this->kelas->setNested("member", "0");
		}*/
		/*$this->kelas->save();*/
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}
		$this->getScheduler()->scheduleDelayedRepeatingTask(new ClosureTask(function() : void{
			foreach($this->getServer()->getOnlinePlayers() as $player){
				$data = date("A");
		        $total = date("g:i");
				if(!$this->Task4->exists(strtolower($player->getName())) or $this->Task4->getNested(strtolower($player->getName())."done") == "false"){
					$x = intval($player->getX());
			        $y = intval($player->getY());
			        $z = intval($player->getZ());
			        /*$uang = $this->uangJajan->getNested(strtolower($player->getName()).".jumlah");*/
				}
				if($this->Task4->getNested(strtolower($player->getName()).".done") === "true"){
					$this->setSB($player);
				}
			}
			$this->getServer()->getAsyncPool()->submitTask(new DayTask());
		}), 20, 20);
		$this->getScheduler()->scheduleDelayedRepeatingTask(new Data(), 20, 20);
		$this->registerNetherite();
		self::$instance = $this;
	}
	
	public static function getInstance(): Main
    {
        return self::$instance;
    }
	
	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool
	{
		switch($cmd->getName()){
			/*case "kantin":
				if(!$sender instanceof Player) return false;
				$this->kantinGUI($sender);*
			break;*/
			case "quest":
				if(!$sender instanceof Player){
					$sender->sendMessage("in game");
				}else{
					$this->Gui($sender);
					$sender->getLevel()->addSound(new QuestOpenSound($sender->x, $sender->y, $sender->z), [$sender]);
				}
			break;
			/*case "kumpulkan":
				if(!$sender instanceof Player) return false;
				if($this->done1($sender)){
					$this->sendVoucher($sender);
					$this->getServer()->broadcastMessage("§7[§6Minefine Quest§7] §a{$p->getName()} Telah Menyelesaikan Tugas1 Dan Mendapatkan Minefine Voucher!");
				}else if($this->done2($sender)){
					$this->sendVoucher($sender);
					$this->getServer()->broadcastMessage("§7[§6Minefine Quest§7] §a{$p->getName()} Telah Menyelesaikan Tugas2 Dan Mendapatkan Minefine Voucher!");
				}else if($this->done3($sender)){
					$this->sendVoucher($sender);
					$this->getServer()->broadcastMessage("§a{$p->getName()} Telah Menyelesaikan Tugas3 Dan Mendapatkan Minefine Voucher!");
				}else if($this->done4($sender)){
					$this->sendVoucher($sender);
					$this->getServer()->broadcastMessage("§7[§6Minefine Quest§7] §a{$p->getName()} Telah Menyelesaikan Tugas4 Dan Mendapatkan Minefine Voucher!");
				}else{
				}
			break;*/ //I Dont Care ;v
		}
		return true;
	}
	
	public function Gui(Player $sender)
	{
		$task1 = Item::get(270, 0, 1);
		$task1->setCustomName("§6§lTugas1§r    §e#1");
		$task1->setLore([
		"§dTugas dasar yang dikerjakan semua murid."
		]);
		$task2 = Item::get(272, 0, 1);
		$task2->setCustomName("§6§lTugas2§r    §e#2");
		$task2->setLore([
			"§dTugas2, Dikerjakan Setelah Tugas 1 di Kerjakan"
		]);
		
		$task3 = Item::get(58, 0, 1);
		$task3->setCustomName("§l§6Tugas3§r    §e#3");
		$task3->setLore([
			"§dSelesaikan tugas kedua untuk mengerjakan tugas ini"
		]);
		
		$task4 = Item::get(297, 0, 1);
		$task4->setCustomName("§l§6Tugas4§r    §e#4");
		$task4->setLore([
			"§dSelesaikan tugas ketiga untuk mengerjakan tugas ini"
		]);
		
		$task5 = Item::get(9, 0, 1);
		$task5->setCustomName("Coming Soon");
		$task5->setLore([
			""
		]);
		$inventory = $this->menu->getInventory();
		$inventory->setItem(7, Item::get(160, 9, 1)->setCustomName(" "));
        
        $inventory->setItem(8, Item::get(262, 0, 1)->setCustomName(" "));
        
        $inventory->setItem(16, Item::get(160, 9, 1)->setCustomName(" "));
        
        $inventory->setItem(25, Item::get(160, 9, 1)->setCustomName(" "));
        
        $inventory->setItem(34, Item::get(160, 9, 1)->setCustomName(" "));
        
        $inventory->setItem(43, Item::get(160, 9, 1)->setCustomName(" "));
        
        $inventory->setItem(44, Item::get(345, 0, 1)->setCustomName(" "));
        
        $inventory->setItem(52, Item::get(160, 9, 1)->setCustomName(" "));
        
        $inventory->setItem(53, Item::get(262, 0, 1)->setCustomName(" "));
		$this->menu->readonly();
        $this->menu->setListener([$this, "GuiListener"]);
        $this->menu->setName("§l§6Quest");
        $inventory = $this->menu->getInventory();
        if(!$this->Task1->exists(strtolower($sender->getName()))){
        	$inventory->setItem(0, $task1);
            $inventory->setItem(1, $task2);
            $inventory->setItem(2, $task3);
            $inventory->setItem(3, $task4);
            $inventory->setItem(4, $task5);
        }else if(
        	$this->Task1->exists(strtolower($sender->getName())) &&
            !$this->Task2->exists(strtolower($sender->getName()))
        ){
        	$inventory->setItem(0, $task2);
            $inventory->setItem(1, $task3);
            $inventory->setItem(2, $task4);
            $inventory->setItem(3, $task5);
            $inventory->removeItem($inventory->getItem(4));
        }else if(
        	$this->Task1->exists(strtolower($sender->getName())) &&
            $this->Task2->exists(strtolower($sender->getName())) &&
            !$this->Task3->exists(strtolower($sender->getName()))
        ){
        	$inventory->setItem(0, $task3);
            $inventory->setItem(1, $task4);
            $inventory->setItem(2, $task5);
            $inventory->removeItem($inventory->getItem(4));
            $inventory->removeItem($inventory->getItem(3));
        }else if(
        	$this->Task1->exists(strtolower($sender->getName())) &&
            $this->Task2->exists(strtolower($sender->getName())) &&
            $this->Task3->exists(strtolower($sender->getName())) &&
            !$this->Task4->exists(strtolower($sender->getName()))
        ){
        	$inventory->setItem(0, $task4);
            $inventory->setItem(1, $task5);
            $inventory->removeItem($inventory->getItem(4));
            $inventory->removeItem($inventory->getItem(3));
            $inventory->removeItem($inventory->getItem(2));
        }else if(
        	$this->Task1->exists(strtolower($sender->getName())) &&
            $this->Task2->exists(strtolower($sender->getName())) &&
            $this->Task3->exists(strtolower($sender->getName())) &&
            $this->Task4->exists(strtolower($sender->getName()))
            ){
        	$inventory->setItem(0, $task5);
            $inventory->removeItem($inventory->getItem(4));
            $inventory->removeItem($inventory->getItem(3));
            $inventory->removeItem($inventory->getItem(2));
            $inventory->removeItem($inventory->getItem(1));
        }
        $this->menu->send($sender);
	}
	
	public function GuiListener(Player $sender, Item $item){
		$inventory = $this->menu->getInventory();
		$task1 = Item::get(270, 0, 1);
		$task1->setCustomName("§6§lTugas1§r    §e#1");
		$task1->setLore([
		"§dTugas dasar yang dikerjakan semua murid."
		]);
		$task2 = Item::get(272, 0, 1);
		$task2->setCustomName("§6§lTugas2§r    §e#2");
		$task2->setLore([
			"§dTugas2, Dikerjakan Setelah Tugas 1 di Kerjakan"
		]);
		
		$task3 = Item::get(58, 0, 1);
		$task3->setCustomName("§l§6Tugas3§r    §e#3");
		$task3->setLore([
			"§dSelesaikan tugas kedua untuk mengerjakan tugas ini"
		]);
		
		$task4 = Item::get(297, 0, 1);
		$task4->setCustomName("§l§6Tugas4§r    §e#4");
		$task4->setLore([
			"§dSelesaikan tugas ketiga untuk mengerjakan tugas ini"
		]);
		
		$task5 = Item::get(9, 0, 1);
		$task5->setCustomName("Coming Soon");
		$task5->setLore([
			""
		]);
		if($item->getid() == 270){
		    $sender->removeAllWindows(true);
		    $sender->getLevel()->addSound(new SoundQuestDone($sender->x, $sender->y, $sender->z), [$sender]);
			if($this->Task1->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§6<§eQuests§6> §aYou have started the quest §eTugas1§6!");
	        //$sender->sendMessage($this->cfg->get("msg1-item1"));
	        
	            $sender->sendMessage("§6<§eQuests§6> §rQuest §eTugas1 §rupdated.");
	        //$sender->sendMessage($this->cfg->get("msg2-item1"));
	        
	            $sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eMining!!! Mining!!!");
				//$sender->removeWindow($inventory);
			}else if($this->Task2->exists(strtolower($sender->getName()))){
				$sender->getLevel()->addSound(new SoundNoMoney($sender));
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKAMU UDAH NGERJAIN TUGAS INI TADI, KAMU MAU BAPAK PUKUL!!");
				$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				//$sender->removeWindow($inventory);
			}else if(!$this->Task1->getNested(strtolower($sender->getName()).".mine") >= "0"){
				$sender->getLevel()->addSound(new ClickSound($sender));
				$this->Task1->setNested(strtolower($sender->getName()).".wood", "100");
			    $this->Task1->setNested(strtolower($sender->getName()).".cobblestone", "320");
			    $this->Task1->setNested(strtolower($sender->getName()).".netherrack", "200");
			    $this->Task1->setNested(strtolower($sender->getName()).".obsidian", "32");
			    $this->Task1->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task1->save();
				$this->setSB($sender);
				$this->pInTask1[$sender->getId()] = $sender;
				$inventory->removeItem($task1);
				$sender->sendMessage("§l§ePAK GURU§r: §eSelesaikan tugas Dengan Baik Dan Benar!");
				$sender->removeAllWindows(true);
			}else{
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKAMU UDAH NGERJAIN TUGAS INI TADI, KAMU MAU BAPAK PUKUL!!");
				$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				$sender->removeAllWindows(true);
				//$sender->removeWindow($inventory);
			}
		}
		if($item->getid() == 272){
		    $sender->removeAllWindows(true);
			$sender->getLevel()->addSound(new SoundQuestDone($sender->x, $sender->y, $sender->z), [$sender]);
			if($this->Task2->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§6<§eQuests§6> §aYou have started the quest §eTugas2§6!");
	        //$sender->sendMessage($this->cfg->get("msg1-item1"));
	        
	            $sender->sendMessage("§6<§eQuests§6> §rQuest §eTugas2 §rupdated.");
	        //$sender->sendMessage($this->cfg->get("msg2-item1"));
	        
	            $sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKerjakan Tugas 2 dengan Baik Dan Benar§6!.");
				//$sender->removeWindow($inventory);
			}else if($this->Task3->exists(strtolower($sender->getName()))){
			    $sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKAMU UDAH NGERJAIN TUGAS INI TADI, KAMU MAU BAPAK PUKUL!!");
				$sender->removeAllWindows(true);
				//$sender->removeWindow($inventory);
			}else if($this->Task1->getNested(strtolower($sender->getName()).".done") === "true"){
				$this->Task2->setNested(strtolower($sender->getName()).".zombie", "25");
				$this->Task2->setNested(strtolower($sender->getName()).".skeleton", "25");
				$this->Task2->setNested(strtolower($sender->getName()).".sheep", "20");
				$this->Task2->setNested(strtolower($sender->getName()).".cow", "25");
				$this->Task2->setNested(strtolower($sender->getName()).".chicken", "15");
				$this->Task2->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task2->save();
				$this->setSB($sender);
				$this->pInTask1[$sender->getId()] = $sender;
				$inventory->removeItem($task2);
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKerjakan Tugas 2 dengan Baik Dan Benar§6!.");
				$sender->removeAllWindows(true);
			}else{
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eSelesaiin tugas pertama dulu!");
				$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				$sender->removeAllWindows(true);
			}
		}
		if($item->getid() == 58){
		    $sender->removeAllWindows(true);
			$sender->getLevel()->addSound(new SoundQuestDone($sender->x, $sender->y, $sender->z), [$sender]);
			if($this->Task3->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§6<§eQuests§6> §aYou have started the quest §eTugas3§6!");
	        //$sender->sendMessage($this->cfg->get("msg1-item1"));
	        
	            $sender->sendMessage("§6<§eQuests§6> §rQuest §eTugas3 §rupdated.");
	        //$sender->sendMessage($this->cfg->get("msg2-item1"));
	        
	            $sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKerjakan Tugas3 dengan Baik Dan Benar§6!.");
				//$sender->removeWindow($inventory);
			}else if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true"){
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKAMU UDAH NGERJAIN TUGAS INI TADI, KAMU MAU BAPAK PUKUL!!");
				$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				$sender->removeAllWindows(true);
				//$sender->removeWindow($inventory);
			}else if($this->Task2->getNested(strtolower($sender->getName()).".done") === "true"){
				$this->Task3->setNested(strtolower($sender->getName()).".stick", "3");
				$this->Task3->setNested(strtolower($sender->getName()).".book", "5");
				$this->Task3->setNested(strtolower($sender->getName()).".bookshelf", "10");
				$this->Task3->setNested(strtolower($sender->getName()).".enchantmenttable", "2");
				$this->Task3->setNested(strtolower($sender->getName()).".compass", "2");
				$this->Task3->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task3->save();
				$this->setSB($sender);
				$this->pInTask1[$sender->getId()] = $sender;
				$inventory->removeItem($task3);
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKerjakan Tugas 3 dengan Baik Dan Benar§6!.");
				//$sender->removeWindow($inventory);
				$sender->removeAllWindows(true);
			}else{
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eSelesaiin tugas Kedua dulu!");
				$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				$sender->removeAllWindows(true);
				//$sender->removeWindow($inventory);
			}
		}
		if($item->getid() == 297){
		    $sender->removeAllWindows(true);
			$sender->getLevel()->addSound(new SoundQuestDone($sender->x, $sender->y, $sender->z), [$sender]);
			if($this->Task4->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§6<§eQuests§6> §aYou have started the quest §eTugas4§6!");
	        //$sender->sendMessage($this->cfg->get("msg1-item1"));
	        
	            $sender->sendMessage("§6<§eQuests§6> §rQuest §eTugas4 §rupdated.");
	        //$sender->sendMessage($this->cfg->get("msg2-item1"));
	        
	            $sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKerjakan Tugas4 dengan Baik Dan Benar§6!.");
				//$sender->removeWindow($inventory);
			}else if($this->Task4->getNested(strtolower($sender->getName()).".done") === "true"){
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKAMU UDAH NGERJAIN TUGAS INI TADI, KAMU MAU BAPAK PUKUL!!");
				$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				$sender->removeAllWindows(true);
				//$sender->removeWindow($inventory);
			}else if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true"){
			    $this->Task4->setNested(strtolower($sender->getName()).".steak", "200");
				$this->Task4->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task4->save();
				$this->setSB($sender);
				$this->pInTask1[$sender->getId()] = $sender;
				$inventory->removeItem($task4);
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eKerjakan Tugas 4 dengan Baik Dan Benar§6!.");
				//$sender->removeWindow($inventory);
				$sender->removeAllWindows(true);
			}else{
				$sender->sendMessage("§6[1/1] §l§ePAK GURU§r: §eSelesaiin tugas Ketiga dulu!");
				$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
				$sender->removeAllWindows(true);
				//$sender->removeWindow($inventory);
			}
		}
		if($item->getid() == 9){
			$sender->getLevel()->addSound(new SoundNoMoney($sender->x, $sender->y, $sender->z), [$sender]);
			$sender->sendMessage("§aComing soon");
			$sender->removeAllWindows(true);
			//$sender->removeWindow($inventory);
		}
	}
	
	/*public function kantinGUI(Player $sender)
	{
		$item1 = Item::get(444, 0, 1);
		$item1->setCustomName("§l§e§oEleytra");
		$item1->setLore([
			"§6Rp100: 1× Eleytra"
		]);
		$item2 = Item::get(354, 0, 1);
		$item2->setCustomName("§l§e§oLegend Cake");
		$item2->setLore([
			"§6Rp20: 1× Legend Cake"
		]);
		
		$item3 = Item::get(742, 0, 1);
		$item3->setCustomName("§l§e§oNetherite Ingot");
		$item3->setLore([
			"§6Rp50: 1× Nether Ingot"
		]);
		$this->kantin->readonly();
        $this->kantin->setListener([$this, "KantinListener"]);
        $this->kantin->setName("§aKantin §l§7|| §r§aMoney: {$this->getUang($sender)}");
        $inventory = $this->kantin->getInventory();
        $inventory->setItem(0, $item1);
        $inventory->setItem(1, $item2);
        $inventory->setItem(2, $item3);
        $this->kantin->send($sender);
	}
	
	public function KantinListener(Player $sender, Item $item): void{
		$inventory = $this->kantin->getInventory();
		if($item->getid() == 742){
			$this->addedItem($sender, 1, 742, 3);
		}
		if($item->getid() == 354){
			$this->sendCake($sender);
		}
		if($item->getid() == 444){
			$this->addedItem($sender, 3, 444, 1);
		}
	}
	
	public function addedItem(Player $sender, int $int, int $item, int $total){
		if($item == 444){
			if($this->getUang($sender) >= 100){
				$this->removeUang($sender, 100);
				$this->uangJajan->save();
				$sender->getInventory()->addItem(Item::get(444, 0, 1));
				$this->getServer()->broadcastMessage("§7[ §eShinka §6Voucher §7] §e{$sender->getName()} Telah menggunakan Uang jajannya untuk membeli 1× Eleytra");
			}else{
				$sender->sendMessage("§7[ §eShinka §6Voucher §7] §cUang jajan kamu tidak cukup nak!");
			}
		}
		if($item == 742){
			if($this->getUang($sender) >= 50){
				$this->removeUang($sender, 50);
				$this->uangJajan->save();
				$sender->sendMessage("§7[ §eShinka §6Voucher §7] §fBerhasil menukarkan uang jajan kamu");
				$sender->getInventory()->addItem(Item::get(742, 0, 1));
			}else{
				$sender->sendMessage("§7[ §eShinka §6Voucher §7] §cUang jajan kamu tidak cukup nak!");
			}
		}
	}
	public function sendCake(Player $sender){
		if($this->getUang($sender) >= 20){
			$this->removeUang($sender, 20);
			$this->uangJajan->save();
			$sender->sendMessage("§7[ §eShinka §6Voucher §7] §fBerhasil menukarkan uang jajan kamu");
			$sender->getInventory()->addItem($this->legendCake($sender));
		}else{
			$sender->sendMessage("§7[ §eShinka §6Voucher §7] §cUang jajan kamu tidak cukup nak!");
		}
	}*/
	
	public function onBannedCommand(PlayerCommandPreprocessEvent $event): void {
		$p = $event->getPlayer();
        $message = $event->getMessage();
        if($message[0] != "/") {
            return;
        }
        if($message[1] != $message[1]) {
            return;
        }
        $command = strtolower(substr($message, 1));
        if($command === "ver" or $command === "pocketmine:ver" or $command === "version" or $command === "pocketmine:version" or $command === "about"){
            $p->sendMessage("§cUnknown command. Try /help for a list of commands");
            $event->setCancelled();
        }
        if($command === "about" or $command === "pocketmine:about"){
        	$p->sendMessage("§cUnknown command. Try /help for a list of commands");
        	$event->setCancelled();
        	$map = $this->getServer()->getCommandMap();
            $c = $map->getCommand($command);
            if($c !== null){
            	$c->setLabel("old_".$command);
                $map->unregister($c);
            }
        }
        if($command === "pl" or $command === "pocketmine:pl" or $command === "plugin" or $command === "pocketmine:plugin"){
        	if($p->isOp()){
        	    return;
        	}else{
        	   $p->sendMessage("§fPlugins (0): ");
               $event->setCancelled();
        	}
        }
    }
	
	/*public function onPlayerChat(PlayerChatEvent $e){
		$p = $e->getPlayer();
		$msg = $e->getMessage();
		$kelas = "kelas";
		if($this->kelas->getNested(strtolower($p->getName()).".kelas") === "kelasA"){
			$kelas = "§c§lKELAS A";
		}else if($this->kelas->getNested(strtolower($p->getName()).".kelas") === "kelasB"){
			$kelas = "§l§bKELAS B";
		}else if($this->kelas->getNested(strtolower($p->getName()).".kelas") === "guru"){
			$kelas = "§l§eGURU";
		}else{ $kelas = "None"; }
		$format = "§7[ {$kelas} §r§7] §f".$p->getName()." §l§6>> §r§f".$msg;
		$e->setFormat($format);
	}*/
	
	/*public function onTouch(PlayerInteractEvent $event){
		$block = $event->getBlock();
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $nametag = $item->getNamedTag();
        
        if($nametag->hasTag("cake", StringTag::class)){
        	$player->addEffect(new EffectInstance(Effect::getEffect(Effect::INSTANT_HEALTH), 19999, 2, false));
            $player->addEffect(new EffectInstance(Effect::getEffect(Effect::SPEED), 19999, 1, false));
            $this->getScheduler()->scheduleRepeatingTask(new setMaxHealth($this, $player->getName()), 130);
            $player->getLevel()->addParticle(new DestroyBlockParticle($player->asVector3(), Block::get(Block::CHEST)));
            $event->setCancelled();
            $item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);
        }
        if($nametag->hasTag("shinka", StringTag::class)){
        	$item->setCount($item->getCount() - 1);
            $player->getInventory()->setItemInHand($item);
        	$this->sendUang($player, 10);
            $this->uangJajan->save();
        	$this->getServer()->broadcastMessage("§7[ §eShinka §6Voucher §7] §e{$player->getName()} Telah menggunakan Vouchernya untuk ditukarkan dengan uang jajan");
        }
	}*/
	
	/*public function sendUang(Player $p, int $n){
		return $this->uangJajan->setNested(strtolower($p->getName()).".jumlah", $this->uangJajan->getAll()[strtolower($p->getName())]["jumlah"] + $n);
	}
	public function removeUang(Player $p, int $n){
		return $this->uangJajan->setNested(strtolower($p->getName()).".jumlah", $this->uangJajan->getAll()[strtolower($p->getName())]["jumlah"] - $n);
	}
	public function getUang(Player $p){
		return $this->uangJajan->getNested(strtolower($p->getName()).".jumlah");
	}
	
	public function onEnterBed(PlayerBedEnterEvent $event)
	{
		$sender = $event->getPlayer();
		$this->getScheduler()->scheduleRepeatingTask(new SleepTask($this, $sender), 10);
		$x = $sender->getX();
		$y = $sender->getY();
		$z = $sender->getZ();
		$sender->setSpawn(new Position($x, $y, $z, $sender->getLevel()));
	}*/
	
	public function onConsume(PlayerItemConsumeEvent $event)
	{
		if($event->isCancelled()) {
            return;
        }
		$p = $event->getPlayer();
		$item = $event->getItem();
		if($item->getId() !== 364){
		    if(
				$this->Task4->exists(strtolower($p->getName())) &&
				!$this->Task4->getAll()[strtolower($p->getName())]["steak"] <= 0
			){
			if($this->Task4->exists(strtolower($p->getName()))){
				$this->Task4->setNested(strtolower($p->getName()).".steak", $this->Task4->getAll()[strtolower($p->getName())]["steak"] - 1);
				$this->Task4->save();
				$this->setSB($p);
				$this->checkTask4($p);
			}
			}
		}
	}
	
	public function Book1(CraftItemEvent $event)
	{
	    if($event->isCancelled()) {
            return;
        }
        $p = $event->getPlayer();
        $items = $event->getOutputs();
        foreach($items as $item){
            if($item->getId() === 340){
            if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["book"] <= 0
			){
					    $this->Task3->setNested(strtolower($p->getName()).".book", $this->Task3->getAll()[strtolower($p->getName())]["book"] - 1);
		                $this->Task3->save();
				        $this->setSB($p);
				        $this->checkTask3($p);
				}
			}
		}
	}
	
	public function Stick1(CraftItemEvent $event)
	{
	    if($event->isCancelled()) {
            return;
        }
        $p = $event->getPlayer();
        $items = $event->getOutputs();
        foreach($items as $item){
            if($item->getId() == 280){
            if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["stick"] <= 0
			){
					    $this->Task3->setNested(strtolower($p->getName()).".stick", $this->Task3->getAll()[strtolower($p->getName())]["stick"] - 1);
		                $this->Task3->save();
				        $this->setSB($p);
				        $this->checkTask3($p);
				}
			}
		}
	}
	
	public function EnchantTable(CraftItemEvent $event)
	{
	    if($event->isCancelled()) {
            return;
        }
        $p = $event->getPlayer();
        $items = $event->getOutputs();
        foreach($items as $item){
            if($item->getId() == 116){
            if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["enchantmenttable"] <= 0
			){
					    $this->Task3->setNested(strtolower($p->getName()).".enchantmenttable", $this->Task3->getAll()[strtolower($p->getName())]["enchantmenttable"] - 1);
		                $this->Task3->save();
				        $this->setSB($p);
				        $this->checkTask3($p);
				}
			}
		}
	}
	
	public function bookshelf1(CraftItemEvent $event)
	{
	    if($event->isCancelled()) {
            return;
        }
        $p = $event->getPlayer();
        $items = $event->getOutputs();
        foreach($items as $item){
            if($item->getId() == 47){
            if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["bookshelf"] <= 0
			){
					    $this->Task3->setNested(strtolower($p->getName()).".bookshelf", $this->Task3->getAll()[strtolower($p->getName())]["bookshelf"] - 1);
		                $this->Task3->save();
				        $this->setSB($p);
				        $this->checkTask3($p);
				}
			}
		}
	}
	
	public function Compass1(CraftItemEvent $event)
	{
	    if($event->isCancelled()) {
            return;
        }
        $p = $event->getPlayer();
        $items = $event->getOutputs();
        foreach($items as $item){
            if($item->getId() == 345){
            if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["compass"] <= 0
			){
					    $this->Task3->setNested(strtolower($p->getName()).".compass", $this->Task3->getAll()[strtolower($p->getName())]["compass"] - 1);
		                $this->Task3->save();
				        $this->setSB($p);
				        $this->checkTask3($p);
				}
			}
		}
	}
	
	/*public function onCraft(CraftItemEvent $event)
	{
		if($event->isCancelled()) {
            return;
        }
		$p = $event->getPlayer();
		$items = $event->getOutputs();
		//print_r($items->getid());
		foreach($items as $item){
			if(!$item instanceof Wood){
				if(!$item instanceof Stick){
				    if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["stick"] <= 0
			){
					    $this->Task1->setNested(strtolower($p->getName()).".stick", $this->Task1->getAll()[strtolower($p->getName())]["stick"] - 1);
		                $this->Task3->save();
				        $this->setSB($p);
				        $this->checkTask3($p);
				    }
				}
			}
			if($item->getId() !== 340){
			    if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["book"] <= 0
			){
					$this->Task3->setNested(strtolower($p->getName()).".book", $this->Task3->getAll()[strtolower($p->getName())]["book"] - 1);
		            $this->Task3->save();
				    $this->setSB($p);
				    $this->checkTask3($p);
				}
			}
			if($item->getId() !== 116){
			    if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["enchantmenttable"] <= 0
			){
					$this->Task3->setNested(strtolower($p->getName()).".enchantmenttable", $this->Task3->getAll()[strtolower($p->getName())]["enchantmenttable"] - 1);
		            $this->Task3->save();
				    $this->setSB($p);
				    $this->checkTask3($p);
				}
			}
			if($item->getId() !== 47){
			    if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["bookshelf"] <= 0
			){
					$this->Task3->setNested(strtolower($p->getName()).".bookshelf", $this->Task3->getAll()[strtolower($p->getName())]["bookshelf"] - 1);
		            $this->Task3->save();
				    $this->setSB($p);
				    $this->checkTask3($p);
				}
			}
			if($item->getId() !== 345){
			    if(
				$this->Task3->exists(strtolower($p->getName())) &&
				!$this->Task3->getAll()[strtolower($p->getName())]["compass"] <= 0
			){
					$this->Task3->setNested(strtolower($p->getName()).".compass", $this->Task3->getAll()[strtolower($p->getName())]["compass"] - 1);
		            $this->Task3->save();
				    $this->setSB($p);
				    $this->checkTask3($p);
				}
			}
		}
    }*/
	
	public function onBreak(BlockBreakEvent $event){
		if($event->isCancelled()) {
            return;
        }
		$p = $event->getPlayer();
		$block = $event->getBlock();
		if($block->getId() === Block::LOG){
			if(
				$this->Task1->exists(strtolower($p->getName())) &&
				!$this->Task1->getAll()[strtolower($p->getName())]["wood"] <= 0
			){
				$this->Task1->setNested(strtolower($p->getName()).".wood", $this->Task1->getAll()[strtolower($p->getName())]["wood"] - 1);
				$this->Task1->save();
				$this->setSB($p);
				$this->checkTask1($p);
			}
		}
		if($block->getId() === 4){
		    if(
				$this->Task1->exists(strtolower($p->getName())) &&
				!$this->Task1->getAll()[strtolower($p->getName())]["cobblestone"] <= 0
			){
			if($this->Task1->exists(strtolower($p->getName()))){
				$this->Task1->setNested(strtolower($p->getName()).".cobblestone", $this->Task1->getAll()[strtolower($p->getName())]["cobblestone"] - 1);
				$this->Task1->save();
				$this->setSB($p);
				$this->checkTask1($p);
			}
			}
		}
		if($block->getId() === 49){
		    if(
				$this->Task1->exists(strtolower($p->getName())) &&
				!$this->Task1->getAll()[strtolower($p->getName())]["obsidian"] <= 0
			){
			if($this->Task1->exists(strtolower($p->getName()))){
				$this->Task1->setNested(strtolower($p->getName()).".obsidian", $this->Task1->getAll()[strtolower($p->getName())]["obsidian"] - 1);
				$this->Task1->save();
				$this->setSB($p);
				$this->checkTask1($p);
			}
			}
		}
		if($block->getId() === 87){
		    if(
				$this->Task1->exists(strtolower($p->getName())) &&
				!$this->Task1->getAll()[strtolower($p->getName())]["netherrack"] <= 0
			){
			if($this->Task1->exists(strtolower($p->getName()))){
				$this->Task1->setNested(strtolower($p->getName()).".netherrack", $this->Task1->getAll()[strtolower($p->getName())]["netherrack"] - 1);
				$this->Task1->save();
				$this->setSB($p);
				$this->checkTask1($p);
			}
			}
		}
	}
	
	public function registerNetherite(): void
	{
		$this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
			[
				'AAA',
				'ABB',
				'BB '
			],
			['A' => Item::get(752, 0, 1), 'B' => Item::get(266, 0, 1)],
			[Item::get(742, 0, 1)])
		);
		$this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_SWORD), Item::get(742, 0, 1)], [Item::get(743, 0, 1)]));
        $this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_SHOVEL), Item::get(742, 0, 1)], [Item::get(744, 0, 1)]));
        $this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_PICKAXE), Item::get(742, 0, 1)], [Item::get(745, 0, 1)]));
        $this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_AXE), Item::get(742, 0, 1)], [Item::get(746, 0, 1)]));

        $this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_HELMET), Item::get(742, 0, 1)], [Item::get(748, 0, 1)]));
        $this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_CHESTPLATE), Item::get(742, 0, 1)], [Item::get(749, 0, 1)]));
        $this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_LEGGINGS), Item::get(742, 0, 1)], [Item::get(750, 0, 1)]));
        $this->getServer()->getCraftingManager()->registerShapelessRecipe(new ShapelessRecipe([Item::get(Item::DIAMOND_BOOTS), Item::get(742, 0, 1)], [Item::get(751, 0, 1)]));
	}
	
	public function setSB($sender)
	{
		$api = Scoreboards::getInstance();
		
		/*if(!$this->Task1->exists(strtolower($sender->getName()))){
			$api->new($sender, "Quest", "§l");
			//$api->setLine($sender, 1, "             ");
			$api->getObjectiveName($sender);
		}*/
		//task 1
		if(!$this->Task1->exists(strtolower($sender->getName()))){
		    $api->new($sender, "Quest", " ");
			$api->getObjectiveName($sender);
		}else{
			$craftq = $this->Task1->getNested(strtolower($sender->getName()).".craft");
			if($craftq <= 0){
				
				$craft = "§aDone";
			}else{
				$craft = $craftq;
			}
			$woodq = $this->Task1->getNested(strtolower($sender->getName()).".wood");
			if($woodq <= 0){
				
				$wood = "§aDone";
			}else{
				$wood = $woodq;
			}
			$cobblestoneq = $this->Task1->getNested(strtolower($sender->getName()).".cobblestone");
			if($cobblestoneq <= 0){
				
				$cobblestone = "§aDone";
			}else{
				$cobblestone = $cobblestoneq;
			}
			$netherrackq = $this->Task1->getNested(strtolower($sender->getName()).".netherrack");
			if($netherrackq <= 0){
				
				$netherrack = "§aDone";
			}else{
				$netherrack = $netherrackq;
			}
			$obsidianq = $this->Task1->getNested(strtolower($sender->getName()).".obsidian");
			if($obsidianq <= 0){
				
				$obsidian = "§aDone";
			}else{
				$obsidian = $obsidianq;
			}
			$day = date("d");
            $month = date("m");
            $year = date("Y");
            $api->new($sender, "Quest", "§l§6Quest  ");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6Tugas1");
			$api->setLine($sender, 4, "  ");
			$api->setLine($sender, 5, "§eMine");
			$api->setLine($sender, 6, "§e- §o§6Oak Wood §ex$wood");
			$api->setLine($sender, 7, "§e- §o§6Cobblestone §ex$cobblestone");
			$api->setLine($sender, 8, "§e- §o§6Netherrack §ex$netherrack");
			
			$api->setLine($sender, 9, "§e- §o§6Obsidian §ex$obsidian");
			$api->getObjectiveName($sender);
		}
		
		//task 2
		if($this->Task1->getNested(strtolower($sender->getName()).".done") === "true"){
			if(!$this->Task2->exists(strtolower($sender->getName()))){
				$api->new($sender, "Quest", " ");
			    $api->getObjectiveName($sender);
			}else if($this->Task1->getNested(strtolower($sender->getName()).".done") === "true" && $this->Task2->exists(strtolower($sender->getName()))){
			    $zombieq = $this->Task2->getNested(strtolower($sender->getName()).".zombie");
			    if($zombieq <= 0){
				    
				    $zombie = "§aDone";
			    }else{
				    $zombie = $zombieq;
			    }
			    $skeletonq = $this->Task2->getNested(strtolower($sender->getName()).".skeleton");
			    if($skeletonq <= 0){
				    
				    $skeleton = "§aDone";
			    }else{
				    $skeleton = $skeletonq;
			    }
			    $sheepq = $this->Task2->getNested(strtolower($sender->getName()).".sheep");
			    if($sheepq <= 0){
				    
				    $sheep = "§aDone";
			    }else{
				    $sheep = $sheepq;
			    }
			    $cowq = $this->Task2->getNested(strtolower($sender->getName()).".cow");
			    if($cowq <= 0){
				    
				    $cow = "§aDone";
			    }else{
				    $cow = $cowq;
			    }
			    $chickenq = $this->Task2->getNested(strtolower($sender->getName()).".chicken");
			    if($chickenq <= 0){
				    
				    $chicken = "§aDone";
			    }else{
				    $chicken = $chickenq;
			    }
			    $day = date("d");
                $month = date("m");
                $year = date("Y");
			    $api->new($sender, "Quest", "§l§6Quest");
			    $api->new($sender, "Quest", "§l§6Quest  ");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6Tugas2");
			$api->setLine($sender, 4, "  ");
			$api->setLine($sender, 5, "§eKill");
			$api->setLine($sender, 6, "§e- §o§6Zombie §ex$zombie");
			$api->setLine($sender, 7, "§e- §o§6Skeleton §ex$skeleton");
			$api->setLine($sender, 8, "§e- §o§6Sheep §ex$sheep");
			
			$api->setLine($sender, 9, "§e- §o§6Cow §ex$cow");
			$api->setLine($sender, 10, "§e- §o§6Chicken §ex$chicken");
			$api->getObjectiveName($sender);
			}
		}
		
		//task 3
		if($this->Task2->getNested(strtolower($sender->getName()).".done") === "true"){
			if(!$this->Task3->exists(strtolower($sender->getName()))){
				$api->new($sender, "Quest", " ");
			    $api->getObjectiveName($sender);
			}else if($this->Task2->getNested(strtolower($sender->getName()).".done") === "true" && $this->Task3->exists(strtolower($sender->getName()))){
				$stickq = $this->Task3->getNested(strtolower($sender->getName()).".stick");
			    if($stickq <= 0){
				    
				    $stick = "§aDone";
			    }else{
				    $stick = $stickq;
			    }
			    $bookq = $this->Task3->getNested(strtolower($sender->getName()).".book");
			    if($bookq <= 0){
				    
				    $book = "§aDone";
			    }else{
				    $book = $bookq;
			    }
			    $bookshelfq = $this->Task3->getNested(strtolower($sender->getName()).".bookshelf");
			    if($bookshelfq <= 0){
				    
				    $bookshelf = "§aDone";
			    }else{
				    $bookshelf = $bookshelfq;
			    }
			    $enchantmenttableq = $this->Task3->getNested(strtolower($sender->getName()).".enchantmenttable");
			    if($enchantmenttableq <= 0){
				    
				    $enchantmenttable = "§aDone";
			    }else{
				    $enchantmenttable = $enchantmenttableq;
			    }
			    $compassq = $this->Task3->getNested(strtolower($sender->getName()).".compass");
			    if($compassq <= 0){
				    
				    $compass = "§aDone";
			    }else{
				    $compass = $compassq;
			    }
			    $day = date("d");
                $month = date("m");
                $year = date("Y");
			    $api->new($sender, "Quest", "§l§6Quest  ");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6Tugas4");
			$api->setLine($sender, 4, "  ");
			$api->setLine($sender, 5, "§eCraft");
			$api->setLine($sender, 6, "§e- §o§6Stick §ex$stick");
			$api->setLine($sender, 7, "§e- §o§6Book §ex$book");
			$api->setLine($sender, 8, "§e- §o§6Bookshelf §ex$bookshelf");
			$api->setLine($sender, 9, "§e- §o§6Enchantment Table §ex$enchantmenttable");
			$api->setLine($sender, 10, "§e- §o§6Compass §ex$compass");
			$api->getObjectiveName($sender);
			}
		}
		
		//task 4
		if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true"){
			if(!$this->Task4->exists(strtolower($sender->getName()))){
				$api->new($sender, "Quest", " ");
			    $api->getObjectiveName($sender);
			}else if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true" && $this->Task4->exists(strtolower($sender->getName()))){
			    $steakq = $this->Task4->getNested(strtolower($sender->getName()).".steak");
			    if($steakq <= 0){
				    
				    $steak = "§aDone";
			    }else{
				    $steak = $steakq;
			    }
			    $day = date("d");
                $month = date("m");
                $year = date("Y");
			    $api->new($sender, "Quest", "§l§6Quest  ");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6Tugas4");
			$api->setLine($sender, 4, "  ");
			$api->setLine($sender, 5, "§eEat");
			$api->setLine($sender, 7, "§e- §o§6Rotten Flesh §ex$steak");
			$api->getObjectiveName($sender);
			}
		}
		
		/*if($this->Task5->getNested(strtolower($sender->getName()).".page") == 1 && $this->Task5->getNested(strtolower($sender->getName()).".done") === "false" && $this->Task5->exists(strtolower($sender->getName()))){
			$day = date("d");
            $month = date("m");
            $year = date("Y");
            $api->new($sender, "Quest", "§l§6Quest");
			$api->setLine($sender, 1, "§7$day $month $year");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6§lTask: §e5");
			$api->setLine($sender, 4, "§7(1/5)");
			$api->setLine($sender, 5, "§6Pergi ke coordinate: §e");
			$api->setLine($sender, 6, "§l§eplay.shinkapoi.xyz:19132");
			$api->getObjectiveName($sender);
		}
		
		if($this->Task5->getNested(strtolower($sender->getName()).".page") == 2 && $this->Task5->getNested(strtolower($sender->getName()).".done") === "false" && $this->Task5->exists(strtolower($sender->getName()))){
			$day = date("d");
            $month = date("m");
            $year = date("Y");
            $api->new($sender, "Quest", "§l§6Quest");
			$api->setLine($sender, 1, "§7$day $month $year");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6§lTask: §e5");
			$api->setLine($sender, 4, "§7(2/4)");
			$api->setLine($sender, 5, "§6Baca buku yang ada di inventory kamu");
			$api->setLine($sender, 6, "§6Note: §eTolong right-click untuk membaca!");
			$api->setLine($sender, 7, "§eJika tidak data kamu akan hilang!");
			$api->setLine($sender, 8, "§l§eplay.shinkapoi.xyz:19132");
			$api->getObjectiveName($sender);
		}
		
		if($this->Task5->getNested(strtolower($sender->getName()).".page") == 3 && $this->Task5->getNested(strtolower($sender->getName()).".done") === "false" && $this->Task5->exists(strtolower($sender->getName()))){
			$doneq = $this->Task5->getNested(strtolower($sender->getName()).".pos");
			if($doneq === "done"){
				$done = "§aDone";
			}else{
				$done = "§cFalse";
			}
			$done1q = $this->Task5->getNested(strtolower($sender->getName()).".pos1");
			if($done1q === "done"){
				$done1 = "§aDone";
			}else{
				$done1 = "§cFalse";
			}
			$day = date("d");
            $month = date("m");
            $year = date("Y");
            $api->new($sender, "Quest", "§l§6Quest");
			$api->setLine($sender, 1, "§7$day $month $year");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6§lTask: §e5");
			$api->setLine($sender, 4, "§7(3/4)");
			$api->setLine($sender, 5, "§6Pos 1: $done");
			$api->setLine($sender, 6, "§6Pos 2: $done1");
			$api->setLine($sender, 7, "§l§eplay.shinkapoi.xyz:19132");
			$api->getObjectiveName($sender);
		}
		
		if($this->Task5->getNested(strtolower($sender->getName()).".page") == 4 && $this->Task5->getNested(strtolower($sender->getName()).".done") === "false" && $this->Task5->exists(strtolower($sender->getName()))){
			$day = date("d");
            $month = date("m");
            $year = date("Y");
            $api->new($sender, "Quest", "§l§6Quest");
			$api->setLine($sender, 1, "§7$day $month $year");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6§lTask: §e5");
			$api->setLine($sender, 4, "§7(4/4)");
			$api->setLine($sender, 5, "§6FAZRIL17: $faz");
			$api->setLine($sender, 6, "§6Kirazuwu: $ki");
			$api->setLine($sender, 7, "§6CrystaBRYT ajg: $cry");
			$api->setLine($sender, 8, "§l§eplay.shinkapoi.xyz:19132");
			$api->getObjectiveName($sender);
		}*/
		
		if($this->Task4->getNested(strtolower($sender->getName()).".done") === "true"){
			$day = date("d");
			$month = date("m");
            $year = date("Y");
            $jam = date("g:i");
            $x = intval($sender->getX());
            $y = intval($sender->getY());
            $z = intval($sender->getZ());
            $online = count($this->getServer()->getOnlinePlayers());
			$api->new($sender, "Quest", "§l§6Quest");
			$api->setLine($sender, 2, "§e- §6§oTunggu Tugas Baru");
			$api->setLine($sender, 3, "§6§oSelanjutnya");
			$api->getObjectiveName($sender);
		}
		return $api;
	}
	
	public function onDamage(EntityDamageEvent $event) {
		$entity = $event->getEntity();
		if($event instanceof EntityDamageByEntityEvent){
			if($entity instanceof Player){
				$dmg = $event->getDamager();
				if($dmg instanceof Player){
					if($this->Task1->getNested(strtolower($dmg->getName()).".done") === "true"){
						if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
						}
					}
				}
			}else{
				if($entity instanceof Painting){
					return false;
				}
				$dmg = $event->getDamager();
				if($dmg instanceof Player){
					if($this->Task1->getNested(strtolower($dmg->getName()).".done") === "true"){
						if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
						}
					}
				}
			}
			if($entity instanceof Zombie){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
						$this->Task2->setNested(strtolower($dmg->getName()).".zombie", $this->Task2->getAll()[strtolower($dmg->getName())]["zombie"] - 1);
						$this->Task2->save();
						$this->setSB($dmg);
						$this->checkTask2($dmg);
					}
				}
			if($entity instanceof Skeleton){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task2->exists(strtolower($dmg->getName()))){
						$this->Task2->setNested(strtolower($dmg->getName()).".skeleton", $this->Task2->getAll()[strtolower($dmg->getName())]["skeleton"] - 1);
						$this->Task2->save();
						$this->setSB($dmg);
						$this->checkTask2($dmg);
					}
				}
			}
			if($entity instanceof Cow){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task2->exists(strtolower($dmg->getName()))){
						$this->Task2->setNested(strtolower($dmg->getName()).".cow", $this->Task2->getAll()[strtolower($dmg->getName())]["cow"] - 1);
						$this->Task2->save();
						$this->setSB($dmg);
						$this->checkTask2($dmg);
					}
				}
			}
			if($entity instanceof Sheep){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task2->exists(strtolower($dmg->getName()))){
						$this->Task2->setNested(strtolower($dmg->getName()).".sheep", $this->Task2->getAll()[strtolower($dmg->getName())]["sheep"] - 1);
						$this->Task2->save();
						$this->setSB($dmg);
						$this->checkTask2($dmg);
					}
				}
			}
			if($entity instanceof Chicken){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task2->exists(strtolower($dmg->getName()))){
						$this->Task2->setNested(strtolower($dmg->getName()).".chicken", $this->Task2->getAll()[strtolower($dmg->getName())]["chicken"] - 1);
						$this->Task2->save();
						$this->setSB($dmg);
						$this->checkTask2($dmg);
					}
				}
			}
		}
	}
	
	/*public function onPickUP(InventoryPickupItemEvent $e){
		if($e->isCancelled()) {
            return;
        }
		$p = $e->getInventory()->getHolder();
		$itemEntity = $e->getItem();
		$item = $itemEntity->getItem();
		if($item->getId() === 363){
			if($this->Task3->exists(strtolower($p->getName()))){
				$this->Task3->setNested(strtolower($p->getName()).".beef", $this->Task3->getAll()[strtolower($p->getName())]["beef"] + 1);
				$this->Task3->save();
				$this->setSB($p);
				$this->checkTask3($p);
			}
		}
		if($item->getId() === 365 || $item->getId() === 288){
			if($this->Task3->exists(strtolower($p->getName()))){
				$this->Task3->setNested(strtolower($p->getName()).".chicken", $this->Task3->getAll()[strtolower($p->getName())]["chicken"] + 1);
				$this->Task3->save();
				$this->setSB($p);
				$this->checkTask3($p);
			}
		}
		if($item->getId() === 35){
			if($this->Task3->exists(strtolower($p->getName()))){
				$this->Task3->setNested(strtolower($p->getName()).".wool", $this->Task3->getAll()[strtolower($p->getName())]["wool"] + 1);
				$this->Task3->save();
				$this->setSB($p);
				$this->checkTask3($p);
			}
		}
		if($item->getId() === 352 || $item->getId() === 262){
			if($this->Task3->exists(strtolower($p->getName()))){
				$this->Task3->setNested(strtolower($p->getName()).".bone", $this->Task3->getAll()[strtolower($p->getName())]["bone"] + 1);
				$this->Task3->save();
				$this->setSB($p);
				$this->checkTask3($p);
			}
		}
		if($item->getId() === 367){
			if($this->Task3->exists(strtolower($p->getName()))){
				$this->Task3->setNested(strtolower($p->getName()).".rotten", $this->Task3->getAll()[strtolower($p->getName())]["rotten"] + 1);
				$this->Task3->save();
				$this->setSB($p);
				$this->checkTask3($p);
			}
		}
	}*/
	
	public function onJoin(PlayerJoinEvent $event){
		$sender = $event->getPlayer();
		$name = strtolower($sender->getName());
		$world = $sender->getLevel()->getName();
		if(!$this->Task1->exists(strtolower($sender->getName()))){
			$this->getScheduler()->scheduleRepeatingTask(new MessageTask($this, $sender->getName()), 10);
			$this->setSB($sender);
		}else{
			$this->setSB($sender);
		}
	}
	
	public function sendVoucher(Player $sender){
		//enchant
		$enchant = Enchantment::getEnchantmentByName("unbreaking");
		$level = 3;
		
		$reward = ItemFactory::get(Item::PAPER, 0);
		$reward->setCustomName("§d§l§oMinefine Voucher");
		$reward->setLore([
			"§eVoucher ini juga bisa Di tukarkan untuk membeli makanan di kantin",
			"§eRight-Click untuk menukarkannya dengan uang jajan",
			"§eJika Tidak Ingin Di Pakai, kumpulkan Dan Tukar Kan Nanti Di Festival Minefine"
		]);
		$reward->addEnchantment(new EnchantmentInstance($enchant, $level));
		$sender->getInventory()->addItem($reward);
		return $reward;
	}
	
	public function checkTask1($p){
		if($this->Task1->getNested(strtolower($p->getName()).".wood") <= 0){
			if($this->Task1->getNested(strtolower($p->getName()).".cobblestone") <= 0){
				if($this->Task1->getNested(strtolower($p->getName()).".netherack") <= 0){
				    if($this->Task1->getNested(strtolower($p->getName()).".obsidian") <= 0){
				        if($this->Task1->getNested(strtolower($p->getName()).".done") === "false"){
				            $this->Task1->setNested(strtolower($p->getName()).".done", "true");
				            $this->Task1->save();
				            $this->sendVoucher($p);
				            $this->setSB($p);
			                $this->getServer()->broadcastMessage("§7[§6Minefine Quest§7] §a{$p->getName()} Telah Menyelesaikan Tugas1 Dan Mendapatkan Minefine Voucher!");
						}
					}
				}
			}
		}
	}
	
	public function checkTask2($p){
		if($this->Task2->getNested(strtolower($p->getName()).".zombie") <= 0){
			if($this->Task2->getNested(strtolower($p->getName()).".skeleton") <= 0){
				if($this->Task2->getNested(strtolower($p->getName()).".creeper") <= 0){
					if($this->Task2->getNested(strtolower($p->getName()).".cow") <= 0){
					    if($this->Task2->getNested(strtolower($p->getName()).".chicken") <= 0){
					        if($this->Task2->getNested(strtolower($p->getName()).".done") === "false"){
					            $this->Task2->setNested(strtolower($p->getName()).".done", "true");
					            $this->Task2->save();
					        $this->sendVoucher($p);
					        $this->setSB($p);
			                $this->getServer()->broadcastMessage("§7[§6Minefine Quest§7] §a{$p->getName()} Telah Menyelesaikan Tugas2 Dan Mendapatkan Minefine Voucher!");
							 }
					    }
					}
				}
			}
		}
	}
	
	public function checkTask3($p){
		if($this->Task3->getNested(strtolower($p->getName()).".stick") <= 0){
			if($this->Task3->getNested(strtolower($p->getName()).".book") >= 0){
				if($this->Task3->getNested(strtolower($p->getName()).".bookshelf") <= 0){
					if($this->Task3->getNested(strtolower($p->getName()).".enchantmenttable") <= 0){
						if($this->Task3->getNested(strtolower($p->getName()).".compass") <= 0){
							if($this->Task3->getNested(strtolower($p->getName()).".done") === "false"){
						       $this->Task3->setNested(strtolower($p->getName()).".done", "true");
						       $this->Task3->save();
					        $this->sendVoucher($p);
					        $this->setSB($p);
			                $this->getServer()->broadcastMessage("§7[§6Minefine Quest§7] §a{$p->getName()} Telah Menyelesaikan Tugas3 Dan Mendapatkan Minefine Voucher!");
							}
						}
					}
				}
			}
		}
	}
	
	public function checkTask4($p){
		if($this->Task4->getNested(strtolower($p->getName()).".steak") <= 0){
		    if($this->Task4->getNested(strtolower($p->getName()).".done") === "false"){
		        $this->Task4->setNested(strtolower($p->getName()).".done", "true");
							$this->Task4->save();
							$this->sendVoucher($p);
							$this->setSB($p);
			                $this->getServer()->broadcastMessage("§7[§6Minefine Quest§7] §a{$p->getName()} Telah Menyelesaikan Tugas4 Dan Mendapatkan Minefine Voucher!");
			}
		}
	}
	
	public function onInteract(PlayerInteractEvent $event){
	    $player = $event->getPlayer();
	    $name = $player->getName();
	    $item = $event->getItem();
	    if($event->getItem()->getId() == "339" and $event->getItem()->getCustomName() == "§d§l§oMinefine Voucher" and $item->getLore([
			"§eVoucher ini juga bisa Di tukarkan untuk membeli makanan di kantin",
			"§eRight-Click untuk menukarkannya dengan uang jajan"
		])){
	        $player->getLevel()->addSound(new SoundQuestDone($player->x, $player->y, $player->z), [$player]);
	        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), "givemoney ".$player->getName()." 10000");
	        $player->getInventory()->removeItem(Item::get(339, 0, 1));
	        $player->sendMessage("§dYou get §a10000 §dMoney from Minefine Voucher");
	        $player->getServer()->broadcastMessage("§7[§6Minefine Voucher§7] §a{$name} §eTelah menggunakan Minefine Voucher");
	    }
	}
	
	public function check1($p){
		if($this->Task1->getNested(strtolower($p->getName()).".wood") == 0){
			if($this->Task1->getNested(strtolower($p->getName()).".cobblestone") == 0){
				if($this->Task1->getNested(strtolower($p->getName()).".netherack") == 0){
				    if($this->Task1->getNested(strtolower($p->getName()).".obsidian") == 0){
				        if($this->Task1->getNested(strtolower($p->getName()).".done") === "false"){
				            $this->Task1->setNested(strtolower($p->getName()).".done", "true");
				            $this->Task1->save();
				            $api = Scoreboards::getInstance();
				            $api->new($p, "Quest", "§l§6Quest   ");
				            $api->setLine($p, 2, "  ");
				            $api->setLine($p, 3, "§6Tugas1");
				            $api->setLine($p, 4, "  ");
				            $api->setLine($p, 5, "§6Mine");
				            $api->setLine($p, 6, "§e- §o§6Talk With NPC Pak Doni");
			                $api->getObjectiveName($p);
				        }
					}
				}
			}
		}
	}
	
	public function done1($p){
	    if($this->Task1->getNested(strtolower($p->getName()).".done") === "true"){
	        $this->sendVoucher($p);
			$this->getServer()->broadcastMessage("§a{$p->getName()} Telah Menyelesaikan Tugas1 Dan Mendapatkan Minefine Voucher!");
		}
	}
	
	public function check2($p){
		if($this->Task2->getNested(strtolower($p->getName()).".zombie") == 0){
			if($this->Task2->getNested(strtolower($p->getName()).".skeleton") == 0){
				if($this->Task2->getNested(strtolower($p->getName()).".creeper") == 0){
					if($this->Task2->getNested(strtolower($p->getName()).".cow") == 0){
					    if($this->Task2->getNested(strtolower($p->getName()).".chicken") == 0){
					        if($this->Task2->getNested(strtolower($p->getName()).".done") === "false"){
					            $this->Task2->setNested(strtolower($p->getName()).".done", "true");
					            $this->Task2->save();
					            $api = Scoreboards::getInstance();
					            $api->new($p, "Quest", "§l§6Quest   ");
				                $api->setLine($p, 2, "  ");
				                $api->setLine($p, 3, "§6Tugas2");
				                $api->setLine($p, 4, "  ");
				                $api->setLine($p, 5, "§6Kill");
				                $api->setLine($p, 6, "§e- §o§6Talk With NPC Pak Doni");
			                    $api->getObjectiveName($p);
					        }
					    }
					}
				}
			}
		}
	}
	
	public function done2($p){
	    if($this->Task2->getNested(strtolower($p->getName()).".done") === "true"){
	        $this->sendVoucher($p);
			$this->getServer()->broadcastMessage("§a{$p->getName()} Telah Menyelesaikan Tugas2 Dan Mendapatkan Minefine Voucher!");
		}
	}
	
	public function check3($p){
		if($this->Task3->getNested(strtolower($p->getName()).".craftingtable") == 0){
			if($this->Task3->getNested(strtolower($p->getName()).".book") == 0){
				if($this->Task3->getNested(strtolower($p->getName()).".bookshelf") == 0){
					if($this->Task3->getNested(strtolower($p->getName()).".enchantmenttable") == 0){
						if($this->Task3->getNested(strtolower($p->getName()).".compass") == 25){
							if($this->Task3->getNested(strtolower($p->getName()).".done") === "false"){
						       $this->Task3->setNested(strtolower($p->getName()).".done", "true");
						       $this->Task3->save();
						       $api = Scoreboards::getInstance();
						       $api->new($p, "Quest", "§l§6Quest   ");
				               $api->setLine($p, 2, "  ");
				               $api->setLine($p, 3, "§6Tugas3");
				               $api->setLine($p, 4, "  ");
				               $api->setLine($p, 5, "§6Craft");
				               $api->setLine($p, 6, "§e- §o§6Talk With NPC Pak Doni");
			                   $api->getObjectiveName($p);
					        }
						}
					}
				}
			}
		}
	}
	
	public function done3($p){
	    if($this->Task3->getNested(strtolower($p->getName()).".done") === "true"){
	        $this->sendVoucher($p);
			$this->getServer()->broadcastMessage("§a{$p->getName()} Telah Menyelesaikan Tugas3 Dan Mendapatkan Minefine Voucher!");
		}
	}
	
	public function check4($p){
		if($this->Task4->getNested(strtolower($p->getName()).".rotten") == 0){
			if($this->Task4->getNested(strtolower($p->getName()).".steak") == 0){
				if($this->Task4->getNested(strtolower($p->getName()).".rawbeef") == 0){
					if($this->Task4->getNested(strtolower($p->getName()).".apple") == 0){
						if($this->Task4->getNested(strtolower($p->getName()).".done") === "false"){
							$this->Task4->setNested(strtolower($p->getName()).".done", "true");
							$this->Task4->save();
							$api = Scoreboards::getInstance();
							$api->new($p, "Quest", "§l§6Quest   ");
				            $api->setLine($p, 2, "  ");
				            $api->setLine($p, 3, "§6Tugas4");
				            $api->setLine($p, 4, "  ");
				            $api->setLine($p, 5, "§6Eat");
				            $api->setLine($p, 6, "§e- §o§6Talk With NPC Pak Doni");
			                $api->getObjectiveName($p);
						}
					}
				}
			}
		}
	}
	
	public function done4($p){
	    if($this->Task4->getNested(strtolower($p->getName()).".done") === "true"){
	        $this->sendVoucher($p);
			$this->getServer()->broadcastMessage("§a{$p->getName()} Telah Menyelesaikan Tugas4 Dan Mendapatkan Minefine Voucher!");
		}
	}
}
