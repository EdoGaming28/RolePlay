<?php

namespace FazrilDev;

 //menu
 use libs\muqsit\invmenu\InvMenu;
 use libs\muqsit\invmenu\InvMenuHandler;
 use libs\form\FormAPI;
 use libs\form\CustomForm;
 use libs\form\SimpleForm;
 use libs\form\Form;
 
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
 
 use pocketmine\item\Item;
 use pocketmine\item\NetheriteHelmet;
 use pocketmine\item\NetheriteChestplate;
 use pocketmine\item\NetheriteLeggings;
 use pocketmine\item\NetheriteBoots;
 use pocketmine\item\Cake;
 use pocketmine\item\Stick;
 use pocketmine\item\Wood;
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
 /*use Miste\scoreboardspe\API\{
	Scoreboard, ScoreboardDisplaySlot, ScoreboardSort, ScoreboardAction
 };*/
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
		$this->kelas = new Config($this->getDataFolder()."kelas.yml", Config::YAML, array());
		$this->Task1 = new Config($this->getDataFolder() . "task1.yml", Config::YAML, array());
		$this->Task2 = new Config($this->getDataFolder() . "task2.yml", Config::YAML, array());
		$this->Task3 = new Config($this->getDataFolder() . "task3.yml", Config::YAML, array());
		$this->Task4 = new Config($this->getDataFolder() . "task4.yml", Config::YAML, array());
		$this->uangJajan = new Config($this->getDataFolder() . "uang.yml", Config::YAML, array());
		if(!$this->kelas->exists("member")){
			$this->kelas->setNested("member", "0");
		}
		$this->kelas->save();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->menu = InvMenu::create(InvMenu::TYPE_CHEST);
		$this->kantin = InvMenu::create(InvMenu::TYPE_CHEST);
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
			        $uang = $this->uangJajan->getNested(strtolower($player->getName()).".jumlah");
			        $player->sendTip("§6Time: §r§o§e{$total} {$data} §l§f|| §r§6Money: §o§e{$uang}§r");
				}
				if($this->Task4->getNested(strtolower($player->getName()).".done") === "true"){
					$this->setSB($player);
				}
				$player->setScoreTag("§7" . round($player->getHealth() / 2, 2) . " §7health");
			}
			$this->getServer()->getAsyncPool()->submitTask(new DayTask());
		}), 20, 20);
		this->getScheduler()->scheduleDelayedRepeatingTask(new Data(), 20, 20);
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
			case "kantin":
				if(!$sender instanceof Player) return false;
				$this->kantinGUI($sender);
			break;
			case "hub":
				if(!$sender instanceof Player){
					$sender->sendMessage("in game");
				}else{
					$x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorX();
					$y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorY();
					$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getFloorZ();
					$level = $this->getServer()->getDefaultLevel();
			        $sender->teleport(new Position($x, $y, $z, $level));
			        $sender->getLevel()->addSound(new EndermanTeleportSound($sender));
			        $sender->sendMessage("§7[§l§eSans§6SMP§r§7] §fKamu telah tiba di sekolah!");
				}
			break;
			case "tugas":
				if(!$sender instanceof Player){
					$sender->sendMessage("in game");
				}else{
					$this->Gui($sender);
					$sender->getLevel()->addSound(new ClickSound($sender));
				}
			break;
			/*case "kartu": 
				if(!$sender instanceof Player){
					$sender->sendMessage("in game");
				}else{
					if($this->kelas->getNested(strtolower($sender->getName()).".nama") === "~"){
						$sender->getLevel()->addSound(new ClickSound($sender));
						$this->formulirUI($sender);
					}else{
						$hs = $args[0] ?? $sender->getName();
					    $p = $this->getServer()->getPlayerExact(strtolower($hs)) ?? $this->getServer()->getPlayerExact(strtolower($hs));
					    if($p->isOnline()){
						    $sender->getLevel()->addSound(new ClickSound($sender));
						    $this->UI($sender, $p);
						}else{
							$sender->sendMessage("§cPlayer not found");
						}
					}
				}
			break;
			case "claim":
				if(!$sender instanceof Player){
					$sender->sendMessage("in game");
				}else{
					if($this->Task1->getNested(strtolower($sender->getName()).".done") === "true"){
						if($this->Task2->exists(strtolower($sender->getName()))){
							if($this->Task3->exists(strtolower($sender->getName()))){
							}else{
								$this->sendVoucher($sender);
								$this->getServer()->broadcastMessage("§6[ §l".$sender->getName()."§r§6 ] §eBerhasil menyelesaikan Task#1");
							}
						}
					}else if($this->Task2->getNested(strtolower($sender->getName()).".done") == "true"){
						if($this->Task1->getNested(strtolower($sender->getName()).".done") == "true"){
							if($this->Task3->exists(strtolower($sender->getName()))){
							}else{
								$this->sendVoucher($sender);
								$this->getServer()->broadcastMessage("§6[ §l".$sender->getName()."§r§6 ] §eBerhasil menyelesaikan Task#2");
							}
						}
					}else if($this->Task3->getNested(strtolower($sender->getName()).".done") == "true"){
						if($this->Task2->getNested(strtolower($sender->getName()).".done") == "true"){
							if($this->Task1->getNested(strtolower($sender->getName()).".done") == "true"){
							}else{
								$this->sendVoucher($sender);
								$this->getServer()->broadcastMessage("§6[ §l".$sender->getName()."§r§6 ] §eBerhasil menyelesaikan Task#3");
							}
						}
					}
				}
			break;*/
			//I don't care ;v
		}
		return true;
	}
	
	public function formulirUI($sender)
	{
		$form = new CustomForm(function(Player $sender, $data){
			if($data === null){
				return true;
			}
			//data diri
			//$array = (array)$data[0];
			$nama = $data[0]; //implode(" ", array_slice($array, 1));
			$umur = $data[1];
			switch($data){
				case 0:
				if(trim($data[0]) === "") {
					$sender->sendMessage("§fTolong isi nama kamu");
					return false;
				}
				if(!is_null($data[0])){
					$this->kelas->setNested(strtolower($sender->getName()).".nama", $nama);
					return true;
				}else{
					$sender->sendMessage("§fMasukan nama kamu menggunakan kalimat");
					return false;
				}
				break;
				case 1:
				if(trim($data[1]) === "") {
					$sender->sendMessage("§fTolong isi umur kamu");
					return false;
				}
				if(is_numeric($data[1])){
					$this->kelas->setNested(strtolower($sender->getName()).".umur", $umur);
					$sender->sendMessage("§a§lDONE: §r§7Berhasil mengisi formulir. Silahkan ketik /kartu untuk melihat data diri kamu");
					return true;
				}else{
					$sender->sendMessage("§fMasukan umur kamu menggunakan angka");
					return false;
				}
				break;
			}
		});
		$form->setTitle("§fFormulir");
        $form->addLabel("\n");
        $form->addInput("\n§enama:", "Type String");
        $form->addInput("\n§eumur:", "Type Int");
        $form->sendToPlayer($sender);
		
	}
	
	public function UI(Player $p, $player)
	{
		$form = (new FormAPI())->createSimpleForm(function (Player $p, $data) {
			if($data === null){
			}
			switch($data){
				case 0:
					$p->getLevel()->addSound(new ClickSound($p));
				return;
			}
		});
		$kelas = $this->kelas->getNested(strtolower($player->getName()).".kelas");
		$umur = $this->kelas->getNested(strtolower($player->getName()).".umur");
		$club = $this->kelas->getNested(strtolower($player->getName()).".club");
		if($kelas === "kelasA"){
			$kelas = "§c§lKELAS A";
		}else if($kelas === "kelasB"){
			$kelas = "§l§bKELAS B";
		}else if($kelas === "guru"){
			$kelas = "§l§eGURU";
		}else{ $kelas = "None"; }
		$form->setTitle("§fData diri {$player->getName()}");
		$form->setContent("§l§aNama: §r§f{$nama}\n§l§aGamertag: §r§f{$player->getName()}\n§l§aUmur: §r§f{$umur}\n§l§aKelas: §r{$kelas}\n§l§aClub: §r{$club}");
		$form->addButton("§l§cKeluar", 0, "textures/ui/cancel");
		$form->sendToPlayer($p);
	}
	
	public function Gui(Player $sender)
	{
		$task1 = Item::get(275, 0, 1);
		$task1->setCustomName("§l§6§oTugas Pemula              §r§7[Task#1]");
		$task1->setLore([
			"§dTugas yang di kerjakan oleh semua murid"
		]);
		$task2 = Item::get(257, 0, 1);
		$task2->setCustomName("§l§6§oMine                                        §r§7[Task#2]");
		$task2->setLore([
			"§dSelesaikan tugas pertama untuk mengerjakan tugas ini"
		]);
		
		$task3 = Item::get(276, 0, 1);
		$task3->setCustomName("§l§6§oHunting                           §r§7[Task#3]");
		$task3->setLore([
			"§dSelesaikan tugas kedua untuk mengerjakan tugas ini"
		]);
		
		$task4 = Item::get(58, 0, 1);
		$task4->setCustomName("§l§6§oCrafting                           §r§7[Task#4]");
		$task4->setLore([
			"§dSelesaikan tugas ketiga untuk mengerjakan tugas ini"
		]);
		
		$task5 = Item::get(513, 0, 1);
		$task5->setCustomName("§l§6§oStoryTask                           §r§7[Task#5]");
		$task5->setLore([
			"§dSelesaikan tugas keempat untuk mengerjakan tugas ini"
		]);
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
            $this->Task4->exists(strtolower($sender->getName())) &&
            !$this->Task5->exists(strtolower($sender->getName()))
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
		$task1 = Item::get(275, 0, 1);
		$task1->setCustomName("§l§6§oTugas Pemula              §r§7[Task#1]");
		$task1->setLore([
			"§dTugas yang di kerjakan oleh semua murid"
		]);
		$task2 = Item::get(257, 0, 1);
		$task2->setCustomName("§l§6§oMine                                        §r§7[Task#2]");
		$task2->setLore([
			"§dSelesaikan tugas pertama untuk mengerjakan tugas ini"
		]);
		
		$task3 = Item::get(276, 0, 1);
		$task3->setCustomName("§l§6§oHunting                           §r§7[Task#3]");
		$task3->setLore([
			"§dSelesaikan tugas kedua untuk mengerjakan tugas ini"
		]);
		
		$task4 = Item::get(58, 0, 1);
		$task4->setCustomName("§l§6§oCrafting                           §r§7[Task#4]");
		$task4->setLore([
			"§dSelesaikan tugas ketiga untuk mengerjakan tugas ini"
		]);
		
		$task5 = Item::get(513, 0, 1);
		$task5->setCustomName("§l§6§oStoryTask                           §r§7[Task#5]");
		$task5->setLore([
			"§dSelesaikan tugas keempat untuk mengerjakan tugas ini"
		]);
		if($item->getid() == 275){
			if($this->Task1->exists(strtolower($sender->getName()))){
				$sender->getLevel()->addSound(new ClickSound($sender));
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sedang mengerjakan tugas ini. KERJAKAN DENGAN BENAR!");
				//$sender->removeWindow($inventory);
			}else if($this->Task2->exists(strtolower($sender->getName()))){
				$sender->getLevel()->addSound(new ClickSound($sender));
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sudah menyelesaikan tugas ini, kamu mau bapak geplak?");
				//$sender->removeWindow($inventory);
			}else if(!$this->Task1->getNested(strtolower($sender->getName()).".makan") >= "0"){
				$sender->getLevel()->addSound(new ClickSound($sender));
				$this->Task1->setNested(strtolower($sender->getName()).".craft", "0");
			    $this->Task1->setNested(strtolower($sender->getName()).".kayu", "0");
			    $this->Task1->setNested(strtolower($sender->getName()).".makan", "0");
			    $this->Task1->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task1->save();
				$this->setSB($sender);
				$inventory->removeItem($task1);
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eSelesaikan tugas ini dengan tepat, cermat dan benar ya nak!");
				//$sender->removeWindow($inventory);
			}else{
				$sender->getLevel()->addSound(new ClickSound($sender));
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sudah menyelesaikan tugas ini, kamu mau bapak geplak?");
				//$sender->removeWindow($inventory);
			}
		}
		if($item->getid() == 257){
			$sender->getLevel()->addSound(new ClickSound($sender));
			if($this->Task2->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sedang mengerjakan tugas ini. KERJAKAN DENGAN BENAR!");
				//$sender->removeWindow($inventory);
			}else if($this->Task3->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sudah menyelesaikan tugas ini, kamu mau bapak geplak?");
				//$sender->removeWindow($inventory);
			}else if($this->Task1->getNested(strtolower($sender->getName()).".done") === "true" && $this->Task1->getNested(strtolower($sender->getName()).".kayu") >= "64"){
				$this->Task2->setNested(strtolower($sender->getName()).".stone", "0");
				$this->Task2->setNested(strtolower($sender->getName()).".obsi", "0");
				$this->Task2->setNested(strtolower($sender->getName()).".netherrack", "0");
				$this->Task2->setNested(strtolower($sender->getName()).".diamond", "0");
				$this->Task2->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task2->save();
				$this->setSB($sender);
				$inventory->removeItem($task2);
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eSelesaikan tugas ini dengan tepat, cermat dan benar ya nak!");
				//$sender->removeWindow($inventory);
			}else{
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eSelesaiin tugas pertama dulu!");
				//$sender->removeWindow($inventory);
			}
		}
		if($item->getid() == 276){
			$sender->getLevel()->addSound(new ClickSound($sender));
			if($this->Task3->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sedang mengerjakan tugas ini. KERJAKAN DENGAN BENAR!");
				//$sender->removeWindow($inventory);
			}else if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true"){
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sudah menyelesaikan tugas ini, kamu mau bapak geplak?");
				//$sender->removeWindow($inventory);
			}else if($this->Task2->getNested(strtolower($sender->getName()).".done") === "true"){
				$this->Task3->setNested(strtolower($sender->getName()).".beef", "0");
				$this->Task3->setNested(strtolower($sender->getName()).".chicken", "0");
				$this->Task3->setNested(strtolower($sender->getName()).".wool", "0");
				$this->Task3->setNested(strtolower($sender->getName()).".bone", "0");
				$this->Task3->setNested(strtolower($sender->getName()).".rotten", "0");
				$this->Task3->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task3->save();
				$this->setSB($sender);
				$inventory->removeItem($task3);
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eSelesaikan tugas ini dengan tepat, cermat dan benar ya nak!");
				//$sender->removeWindow($inventory);
			}else{
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eSelesaiin tugas kedua dulu!");
				//$sender->removeWindow($inventory);
			}
		}
		if($item->getid() == 58){
			$sender->getLevel()->addSound(new ClickSound($sender));
			if($this->Task4->exists(strtolower($sender->getName()))){
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sedang mengerjakan tugas ini. KERJAKAN DENGAN BENAR!");
				//$sender->removeWindow($inventory);
			}else if($this->Task4->getNested(strtolower($sender->getName()).".done") === "true"){
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eKamu sudah menyelesaikan tugas ini, kamu mau bapak geplak?");
				//$sender->removeWindow($inventory);
			}else if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true"){
				$this->Task4->setNested(strtolower($sender->getName()).".helmet", "0");
				$this->Task4->setNested(strtolower($sender->getName()).".baju", "0");
				$this->Task4->setNested(strtolower($sender->getName()).".celana", "0");
				$this->Task4->setNested(strtolower($sender->getName()).".boots", "0");
				$this->Task4->setNested(strtolower($sender->getName()).".done", "false");
				$this->Task4->save();
				$this->setSB($sender);
				$inventory->removeItem($task4);
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eSelesaikan tugas ini dengan tepat, cermat dan benar ya nak!");
				//$sender->removeWindow($inventory);
			}else{
				$sender->sendMessage("§7§l[§r§6 Pak Sunardi§l§7 ] §eSelesaiin tugas ketiga dulu!");
				//$sender->removeWindow($inventory);
			}
		}
		if($item->getid() == 513){
			$sender->getLevel()->addSound(new ClickSound($sender));
			$sender->sendMessage("§aComing soon, please stay tun");
			//$sender->removeWindow($inventory);
		}
	}
	
	public function kantinGUI(Player $sender)
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
	}
	
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
        if($command === "ver" or $command === "pocketmine:ver" or $command === "version" or $command === "pocketmine:version"){
            $p->sendMessage("This server is running PocketMine-MP 3.14.2 for Minecraft: Bedrock Edition v1.16.0 (protocol version 407)");
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
        	   $p->sendMessage("§fPlugins (7): §aDevTools v1.14.1, FormAPI v1.3.0, WaterDogAcceptPlayerProtocol_ByFazrilDev v1.2, PureEntitiesX v0.6.7, RolePlay_ByFazrilDev v1.5, Scoreboards v1.0.2, LayAndSit_ByFazrilDev v1");
               $event->setCancelled();
        	}
        }
    }
	
	public function onPlayerChat(PlayerChatEvent $e){
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
	}
	
	public function onTouch(PlayerInteractEvent $event){
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
	}
	
	public function sendUang(Player $p, int $n){
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
	}
	
	public function onConsume(PlayerItemConsumeEvent $event)
	{
		if($event->isCancelled()) {
            return;
        }
		$p = $event->getPlayer();
		$item = $event->getItem();
		if($item->getId() !== 373){
			if($this->Task1->exists(strtolower($p->getName()))){
				$this->Task1->setNested(strtolower($p->getName()).".makan", $this->Task1->getAll()[strtolower($p->getName())]["makan"] + 1);
				$this->Task1->save();
				$this->setSB($p);
				$this->checkTask1($p);
			}
		}
	}
	
	public function onCraft(CraftItemEvent $event)
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
					if($this->Task1->exists(strtolower($p->getName()))){
					    $this->Task1->setNested(strtolower($p->getName()).".craft", $this->Task1->getAll()[strtolower($p->getName())]["craft"] + 1);
		                $this->Task1->save();
				        $this->setSB($p);
				        $this->checkTask1($p);
				    }
				}
			}
			if($item instanceof NetheriteHelmet){
				if($this->Task4->exists(strtolower($p->getName()))){
					$this->Task4->setNested(strtolower($p->getName()).".helmet", $this->Task4->getAll()[strtolower($p->getName())]["helmet"] + 1);
		            $this->Task4->save();
				    $this->setSB($p);
				    $this->checkTask4($p);
				}
			}
			if($item instanceof NetheriteChestplate){
				if($this->Task4->exists(strtolower($p->getName()))){
					$this->Task4->setNested(strtolower($p->getName()).".baju", $this->Task4->getAll()[strtolower($p->getName())]["baju"] + 1);
		            $this->Task4->save();
				    $this->setSB($p);
				    $this->checkTask4($p);
				}
			}
			if($item instanceof NetheriteLeggings){
				if($this->Task4->exists(strtolower($p->getName()))){
					$this->Task4->setNested(strtolower($p->getName()).".celana", $this->Task4->getAll()[strtolower($p->getName())]["celana"] + 1);
		            $this->Task4->save();
				    $this->setSB($p);
				    $this->checkTask4($p);
				}
			}
			if($item instanceof NetheriteBoots){
				if($this->Task4->exists(strtolower($p->getName()))){
					$this->Task4->setNested(strtolower($p->getName()).".boots", $this->Task4->getAll()[strtolower($p->getName())]["boots"] + 1);
		            $this->Task4->save();
				    $this->setSB($p);
				    $this->checkTask4($p);
				}
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event){
		if($event->isCancelled()) {
            return;
        }
		$p = $event->getPlayer();
		$block = $event->getBlock();
		if($block->getId() === Block::LOG){
			if($this->Task1->exists(strtolower($p->getName()))){
				$this->Task1->setNested(strtolower($p->getName()).".kayu", $this->Task1->getAll()[strtolower($p->getName())]["kayu"] + 1);
				$this->Task1->save();
				$this->setSB($p);
				$this->checkTask1($p);
			}
		}
		if($block->getId() === Block::LOG2){
			if($this->Task1->exists(strtolower($p->getName()))){
				$this->Task1->setNested(strtolower($p->getName()).".kayu", $this->Task1->getAll()[strtolower($p->getName())]["kayu"] + 1);
				$this->Task1->save();
				$this->setSB($p);
				$this->checkTask1($p);
			}
		}
		if($block->getId() === 4){
			if($this->Task2->exists(strtolower($p->getName()))){
				$this->Task2->setNested(strtolower($p->getName()).".stone", $this->Task2->getAll()[strtolower($p->getName())]["stone"] + 1);
				$this->Task2->save();
				$this->setSB($p);
				$this->checkTask2($p);
			}
		}
		if($block->getId() === 49){
			if($this->Task2->exists(strtolower($p->getName()))){
				$this->Task2->setNested(strtolower($p->getName()).".obsi", $this->Task2->getAll()[strtolower($p->getName())]["obsi"] + 1);
				$this->Task2->save();
				$this->setSB($p);
				$this->checkTask2($p);
			}
		}
		if($block->getId() === 87){
			if($this->Task2->exists(strtolower($p->getName()))){
				$this->Task2->setNested(strtolower($p->getName()).".netherrack", $this->Task2->getAll()[strtolower($p->getName())]["netherrack"] + 1);
				$this->Task2->save();
				$this->setSB($p);
				$this->checkTask2($p);
			}
		}
		if($block->getId() === Block::DIAMOND_ORE){
			if($this->Task2->exists(strtolower($p->getName()))){
				$this->Task2->setNested(strtolower($p->getName()).".diamond", $this->Task2->getAll()[strtolower($p->getName())]["diamond"] + 1);
				$this->Task2->save();
				$this->setSB($p);
				$this->checkTask2($p);
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
			if($craftq >= 3){
				
				$craft = "§aDONE";
			}else{
				$craft = "§e".$craftq."§e/3";
			}
			$kayuq = $this->Task1->getNested(strtolower($sender->getName()).".kayu");
			if($kayuq >= 64){
				
				$kayu = "§aDONE";
			}else{
				$kayu = "§e".$kayuq."§e/64";
			}
			$makanq = $this->Task1->getNested(strtolower($sender->getName()).".makan");
			if($makanq >= 12){
				
				$makan = "§aDONE";
			}else{
				$makan = "§e".$makanq."§e/12";
			}
			$day = date("d");
            $month = date("m");
            $year = date("Y");
            $api->new($sender, "Quest", "§l§6Quest");
			$api->setLine($sender, 1, "§7$day $month $year");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§l§6Task: §e1");
			$api->setLine($sender, 4, "§6§lNewbie");
			$api->setLine($sender, 5, "§6Crafting: §e$craft");
			$api->setLine($sender, 6, "§6Collect Log: §e$kayu");
			$api->setLine($sender, 7, "§6Eat: §e$makan");
			$api->setLine($sender, 8, "               ");
			$api->setLine($sender, 9, "§l§eplay.shinkapoi.xyz:19132");
			$api->getObjectiveName($sender);
		}
		
		//task 2
		if($this->Task1->getNested(strtolower($sender->getName()).".done") === "true"){
			if(!$this->Task2->exists(strtolower($sender->getName()))){
				$api->new($sender, "Quest", " ");
			    $api->getObjectiveName($sender);
			}else if($this->Task1->getNested(strtolower($sender->getName()).".done") === "true" && $this->Task2->exists(strtolower($sender->getName()))){
			    $stoneq = $this->Task2->getNested(strtolower($sender->getName()).".stone");
			    if($stoneq >= 268){
				    
				    $stone = "§aDONE";
			    }else{
				    $stone = "§e".$stoneq."§e/268";
			    }
			    $obsiq = $this->Task2->getNested(strtolower($sender->getName()).".obsi");
			    if($obsiq >= 32){
				    
				    $obsi = "§aDONE";
			    }else{
				    $obsi = "§e".$obsiq."§e/32";
			    }
			    $netherq = $this->Task2->getNested(strtolower($sender->getName()).".netherrack");
			    if($netherq >= 320){
				    
				    $nether = "§aDONE";
			    }else{
				    $nether = "§e".$netherq."§e/320";
			    }
			    $dmq = $this->Task2->getNested(strtolower($sender->getName()).".diamond");
			    if($dmq >= 64){
				    
				    $dm = "§aDONE";
			    }else{
				    $dm = "§e".$dmq."§e/64";
			    }
			    $day = date("d");
                $month = date("m");
                $year = date("Y");
			    $api->new($sender, "Quest", "§l§6Quest");
			    $api->setLine($sender, 1, "§7$day $month $year");
			    $api->setLine($sender, 2, "          ");
			    $api->setLine($sender, 3, "§6§lTask: §e2");
			    $api->setLine($sender, 4, "§6§lMining");
			    $api->setLine($sender, 5, "§6Cobblestone: §e$stone");
			    $api->setLine($sender, 6, "§6Obsidian: §e$obsi");
			    $api->setLine($sender, 7, "§6Diamond Ore: §e$dm");
			    $api->setLine($sender, 8, "§6Netherrack: §e$nether");
			    $api->setLine($sender, 9, "               ");
			    $api->setLine($sender, 10, "§l§eplay.shinkapoi.xyz:19132");
			    $api->getObjectiveName($sender);
			}
		}
		
		//task 3
		if($this->Task2->getNested(strtolower($sender->getName()).".done") === "true"){
			if(!$this->Task3->exists(strtolower($sender->getName()))){
				$api->new($sender, "Quest", " ");
			    $api->getObjectiveName($sender);
			}else if($this->Task2->getNested(strtolower($sender->getName()).".done") === "true" && $this->Task3->exists(strtolower($sender->getName()))){
				$beefq = $this->Task3->getNested(strtolower($sender->getName()).".beef");
			    if($beefq >= 25){
				    
				    $beef = "§aDONE";
			    }else{
				    $beef = "§e".$beefq."§e/25";
			    }
			    $chickenq = $this->Task3->getNested(strtolower($sender->getName()).".chicken");
			    if($chickenq >= 15){
				    
				    $chicken = "§aDONE";
			    }else{
				    $chicken = "§e".$chickenq."§e/15";
			    }
			    $woolq = $this->Task3->getNested(strtolower($sender->getName()).".wool");
			    if($woolq >= 15){
				    
				    $wool = "§aDONE";
			    }else{
				    $wool = "§e".$woolq."§e/15";
			    }
			    $boneq = $this->Task3->getNested(strtolower($sender->getName()).".bone");
			    if($boneq >= 25){
				    
				    $bone = "§aDONE";
			    }else{
				    $bone = "§e".$boneq."§e/25";
			    }
			    $rottenq = $this->Task3->getNested(strtolower($sender->getName()).".rotten");
			    if($rottenq >= 25){
				    
				    $rotten = "§aDONE";
			    }else{
				    $rotten = "§e".$rottenq."§e/25";
			    }
			    $day = date("d");
                $month = date("m");
                $year = date("Y");
			    $api->new($sender, "Quest", "§l§6Quest");
			    $api->setLine($sender, 1, "§7$day $month $year");
			    $api->setLine($sender, 2, "          ");
			    $api->setLine($sender, 3, "§6§lTask: §e3");
			    $api->setLine($sender, 4, "§6§lHunting");
			    $api->setLine($sender, 5, "§6Cow: §e$beef");
			    $api->setLine($sender, 6, "§6Chicken: §e$chicken");
			    $api->setLine($sender, 7, "§6Sheep: §e$wool");
			    $api->setLine($sender, 8, "§6Skeleton: §e$bone");
			    $api->setLine($sender, 9, "§6Zombie: §e$rotten");
			    $api->setLine($sender, 10, "               ");
		        $api->setLine($sender, 11, "§l§eplay.shinkapoi.xyz:19132");
			    $api->getObjectiveName($sender);
			}
		}
		
		//task 4
		if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true"){
			if(!$this->Task4->exists(strtolower($sender->getName()))){
				$api->new($sender, "Quest", " ");
			    $api->getObjectiveName($sender);
			}else if($this->Task3->getNested(strtolower($sender->getName()).".done") === "true" && $this->Task4->exists(strtolower($sender->getName()))){
				$helmetq = $this->Task4->getNested(strtolower($sender->getName()).".helmet");
			    if($helmetq >= 1){
				    
				    $helmet = "§aDONE";
			    }else{
				    $helmet = "§e".$helmetq."§e/1";
			    }
			    $chestq = $this->Task4->getNested(strtolower($sender->getName()).".baju");
			    if($chestq >= 1){
				    
				    $chest = "§aDONE";
			    }else{
				    $chest = "§e".$chestq."§e/1";
			    }
			    $legq = $this->Task4->getNested(strtolower($sender->getName()).".celana");
			    if($legq >= 1){
				    
				    $leg = "§aDONE";
			    }else{
				    $leg = "§e".$legq."§e/1";
			    }
			    $bootq = $this->Task4->getNested(strtolower($sender->getName()).".boots");
			    if($bootq >= 1){
				    
				    $boot = "§aDONE";
			    }else{
				    $boot = "§e".$bootq."§e/1";
			    }
			    $day = date("d");
                $month = date("m");
                $year = date("Y");
			    $api->new($sender, "Quest", "§l§6Quest");
			    $api->setLine($sender, 1, "§7$day $month $year");
			    $api->setLine($sender, 2, "          ");
			    $api->setLine($sender, 3, "§6§lTask: §e4");
			    $api->setLine($sender, 4, "§6§lCrafting Netherite");
			    $api->setLine($sender, 5, "§6Helmet: §e$helmet");
			    $api->setLine($sender, 6, "§6Chestplate: §e$chest");
			    $api->setLine($sender, 7, "§6Leggings: §e$leg");
			    $api->setLine($sender, 8, "§6Boots: §e$boot");
			    $api->setLine($sender, 9, "               ");
		        $api->setLine($sender, 10, "§l§eplay.shinkapoi.xyz:19132");
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
			$api->new($sender, "Quest", "§l§6Quest §a§oDONE");
			$api->setLine($sender, 1, "§7$day $month $year || {$jam}");
			$api->setLine($sender, 2, "          ");
			$api->setLine($sender, 3, "§6Quest: §eTunggu tugas baru selanjutnya!");
			$api->setLine($sender, 4, "§6Name: §e{$sender->getName()}");
			$api->setLine($sender, 5, "§6Uang: §e{$this->getUang($sender)}");
			$api->setLine($sender, 6, "§6Online: §e{$online}/50");
			$api->setLine($sender, 7, "       ");
			$api->setLine($sender, 8, "§l§eplay.shinkapoi.xyz:19132");
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
					if($this->Task4->getNested(strtolower($dmg->getName()).".done") === "true"){
						if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
							$dmg->sendPopUp("§aMoney +5");
							$this->sendUang($dmg, 5);
							$this->uangJajan->save();
						}
					}
				}
			}else{
				if($entity instanceof Painting){
					return false;
				}
				$dmg = $event->getDamager();
				if($dmg instanceof Player){
					if($this->Task4->getNested(strtolower($dmg->getName()).".done") === "true"){
						if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
							$dmg->sendPopup("§aMoney +2");
							$this->sendUang($dmg, 2);
							$this->uangJajan->save();
						}
					}
				}
			}
			if($entity instanceof Zombie){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task3->exists(strtolower($dmg->getName()))){
						$this->Task3->setNested(strtolower($dmg->getName()).".rotten", $this->Task3->getAll()[strtolower($dmg->getName())]["rotten"] + 1);
						$this->Task3->save();
						$this->setSB($dmg);
						$this->checkTask3($dmg);
					}
				}
			}
			if($entity instanceof Skeleton){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task3->exists(strtolower($dmg->getName()))){
						$this->Task3->setNested(strtolower($dmg->getName()).".bone", $this->Task3->getAll()[strtolower($dmg->getName())]["bone"] + 1);
						$this->Task3->save();
						$this->setSB($dmg);
						$this->checkTask3($dmg);
					}
				}
			}
			if($entity instanceof Cow){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task3->exists(strtolower($dmg->getName()))){
						$this->Task3->setNested(strtolower($dmg->getName()).".beef", $this->Task3->getAll()[strtolower($dmg->getName())]["beef"] + 1);
						$this->Task3->save();
						$this->setSB($dmg);
						$this->checkTask3($dmg);
					}
				}
			}
			if($entity instanceof Sheep){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task3->exists(strtolower($dmg->getName()))){
						$this->Task3->setNested(strtolower($dmg->getName()).".wool", $this->Task3->getAll()[strtolower($dmg->getName())]["wool"] + 1);
						$this->Task3->save();
						$this->setSB($dmg);
						$this->checkTask3($dmg);
					}
				}
			}
			if($entity instanceof Chicken){
				$dmg = $event->getDamager();
				if (($entity->getHealth() - $event->getFinalDamage()) <= 0) {
					if($this->Task3->exists(strtolower($dmg->getName()))){
						$this->Task3->setNested(strtolower($dmg->getName()).".chicken", $this->Task3->getAll()[strtolower($dmg->getName())]["chicken"] + 1);
						$this->Task3->save();
						$this->setSB($dmg);
						$this->checkTask3($dmg);
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
		if(!$this->uangJajan->exists(strtolower($sender->getName()))){
			$this->uangJajan->setNested(strtolower($sender->getName()).".jumlah", "0");
			$this->uangJajan->save();
		}
		if($sender->isOp()){
			if(!$this->kelas->exists(strtolower($sender->getName()))){
				$this->kelas->setNested($name.".nama", $sender->getName());
				$this->kelas->setNested($name.".kelas", "guru");
				$this->kelas->setNested($name.".club", "None");
				$this->kelas->setNested($name.".umur", "None");
				$this->kelas->save();
			}
		}else if(!$this->kelas->exists(strtolower($sender->getName()))){
			$r = mt_rand(1, 4);
			switch($r){
				case 1:
					$this->kelas->setNested($name.".nama", "~");
					$this->kelas->setNested($name.".kelas", "kelasA");
					$this->kelas->setNested($name.".club", "~");
					$this->kelas->setNested($name.".umur", "~");
					$this->kelas->setNested("member", $this->kelas->getAll()["member"] + 1);
					$this->kelas->save();
				break;
				case 2:
					$this->kelas->setNested($name.".nama", "~");
					$this->kelas->setNested($name.".kelas", "kelasB");
					$this->kelas->setNested($name.".club", "~");
					$this->kelas->setNested($name.".umur", "~");
					$this->kelas->setNested("member", $this->kelas->getAll()["member"] + 1);
					$this->kelas->save();
				break;
				case 3:
					$this->kelas->setNested($name.".nama", "~");
					$this->kelas->setNested($name.".kelas", "kelasA");
					$this->kelas->setNested($name.".club", "~");
					$this->kelas->setNested($name.".umur", "~");
					$this->kelas->setNested("member", $this->kelas->getAll()["member"] + 1);
					$this->kelas->save();
				break;
				case 4:
					$this->kelas->setNested($name.".nama", "~");
					$this->kelas->setNested($name.".kelas", "kelasB");
					$this->kelas->setNested($name.".club", "~");
					$this->kelas->setNested($name.".umur", "~");
					$this->kelas->setNested("member", $this->kelas->getAll()["member"] + 1);
					$this->kelas->save();
				break;
			}
		}
		if($this->kelas->exists(strtolower($sender->getName()))){
			if($this->kelas->getNested(strtolower($sender->getName()).".kelas") === "kelasA"){
				$sender->setDisplayName("§7[§cKELAS A§7] §f".$sender->getName());
				$sender->setNameTag("§7[§cKELAS A§7] §f".$sender->getName());
			}else if($this->kelas->getNested(strtolower($sender->getName()).".kelas") === "kelasB"){
				$sender->setDisplayName("§7[§3KELAS B§7] §f".$sender->getName());
				$sender->setNameTag("§7[§3KELAS B§7] §f".$sender->getName());
			}else if($this->kelas->getNested(strtolower($sender->getName()).".kelas") === "guru"){
				$sender->setDisplayName("§7[§6GURU§7] §f".$sender->getName());
				$sender->setNameTag("§7[§6GURU§7] §f".$sender->getName());
			}else{ $sender->sendMessage("none"); }
		}
	}
	
	public function legendCake(Player $sender){
		$enchant = Enchantment::getEnchantmentByName("unbreaking");
		$level = 3;
		
		$cake = ItemFactory::get(354, 0, 1);
		$cake->getNamedTag()->setString("cake", "legend");
		$cake->setCustomName("§eLegend §6Cake");
		$cake->setLore([
			"§dHaste +20%",
			"§dMax health +20%",
			"§dSpeed +10%",
			"\n§fRight-Click for eat"
		]);
		$cake->addEnchantment(new EnchantmentInstance($enchant, $level));
		return $cake;
	}
	
	public function sendVoucher(Player $sender){
		//enchant
		$enchant = Enchantment::getEnchantmentByName("unbreaking");
		$level = 3;
		
		$reward = ItemFactory::get(Item::PAPER, 0);
		$reward->getNamedTag()->setString("shinka", "voucher");
		$reward->setCustomName("§bShinka §6Voucher");
		$reward->setLore([
			"§eVoucher ini bisa untuk membeli makanan, lahan dan barang-barang lainnya",
			"§eRight-Click untuk menukarkannya dengan uang saku"
		]);
		$reward->addEnchantment(new EnchantmentInstance($enchant, $level));
		$sender->getInventory()->addItem($reward);
		return $reward;
	}
	
	public function checkTask1($p){
		if($this->Task1->getNested(strtolower($p->getName()).".craft") >= 3){
			if($this->Task1->getNested(strtolower($p->getName()).".kayu") >= 64){
				if($this->Task1->getNested(strtolower($p->getName()).".makan") >= 12){
					if($this->Task1->getNested(strtolower($p->getName()).".done") === "false"){
						$this->Task1->setNested(strtolower($p->getName()).".done", "true");
					    $this->Task1->save();
					    $this->sendVoucher($p);
						$this->setSB($p);
					    $this->getServer()->broadcastMessage("§7[ §6§l".$p->getName()."§r§7 ] §eBerhasil menyelesaikan Task#1");
					}
				}
			}
		}
	}
	public function checkTask2($p){
		if($this->Task2->getNested(strtolower($p->getName()).".stone") >= 268){
			if($this->Task2->getNested(strtolower($p->getName()).".obsi") >= 32){
				if($this->Task2->getNested(strtolower($p->getName()).".diamond") >= 64){
					if($this->Task2->getNested(strtolower($p->getName()).".netherrack") >= 320){
						if($this->Task2->getNested(strtolower($p->getName()).".done") === "false"){
						    $this->Task2->setNested(strtolower($p->getName()).".done", "true");
					        $this->Task2->save();
					        $this->sendVoucher($p);
						    $this->setSB($p);
					        $this->getServer()->broadcastMessage("§7[ §6§l".$p->getName()."§r§7 ] §eBerhasil menyelesaikan Task#2");
					    }
					}
				}
			}
		}
	}
	public function checkTask3($p){
		if($this->Task3->getNested(strtolower($p->getName()).".beef") >= 25){
			if($this->Task3->getNested(strtolower($p->getName()).".chicken") >= 15){
				if($this->Task3->getNested(strtolower($p->getName()).".wool") >= 15){
					if($this->Task3->getNested(strtolower($p->getName()).".bone") >= 25){
						if($this->Task3->getNested(strtolower($p->getName()).".rotten") >= 25){
							if($this->Task3->getNested(strtolower($p->getName()).".done") === "false"){
						       $this->Task3->setNested(strtolower($p->getName()).".done", "true");
					           $this->Task3->save();
					           $this->sendVoucher($p);
					           $this->setSB($p);
					           $this->getServer()->broadcastMessage("§7[ §6§l".$p->getName()."§r§7 ] §eBerhasil menyelesaikan Task#3");
					        }
						}
					}
				}
			}
		}
	}
	
	public function checkTask4($p){
		if($this->Task4->getNested(strtolower($p->getName()).".helmet") >= 1){
			if($this->Task4->getNested(strtolower($p->getName()).".baju") >= 1){
				if($this->Task4->getNested(strtolower($p->getName()).".celana") >= 1){
					if($this->Task4->getNested(strtolower($p->getName()).".boots") >= 1){
						if($this->Task4->getNested(strtolower($p->getName()).".done") === "false"){
							$this->Task4->setNested(strtolower($p->getName()).".done", "true");
							$this->Task4->save();
							$this->sendVoucher($p);
							$this->setSB($p);
							$this->getServer()->broadcastMessage("§7[ §6§l".$p->getName()."§r§7 ] §eBerhasil menyelesaikan Task#4");
						}
					}
				}
			}
		}
	}
}
