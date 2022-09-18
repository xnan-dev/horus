<?php
namespace xnan\Trurl\Horus\BotWorld;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Portfolio;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\MarketTrader;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Horus\BotArena;
use xnan\Trurl\Horus\WorldSettings;
use xnan\Trurl\Horus\Builders;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Asset\Functions::Load;
BotArena\Functions::Load;
Builders\Functions::Load;
use xnan\Trurl\Horus\Persistence;

class Functions { const Load=1; }

class BotWorld {
	private $botArenas;
	private $settings=null;	
	private static $instance;
	private $pdo;

	function __construct() {
		srand(0);
		$this->settings=new WorldSettings\WorldSettings();		
		$this->setupSettings();				
	}

	function pdo(&$pdo=null) {
		if ($pdo!=null) {
			$this->pdo=$pdo;
		} else {
			if ($this->pdo==null) {
				throw new \Exception("pdo not yet setup");
			}
		}

		return $this->pdo;
	}	


	function persistence() {
		return Persistence\Persistence::instance();
	}

	function afterPdoSetup() {
		$this->setupBotArenas();
	}

	private function setupBotArenas() {		
		$this->botArenas=Persistence\Persistence::instance()->worldBotArenas();
	}
	
	static function instance() {
		if (BotWorld::$instance==null)  {			
			BotWorld::$instance=new BotWorld();				
		}
		return BotWorld::$instance;
	}

	function botArenas() {
		return $this->botArenas;
	}

	function setupSettings() {
		$s=$this->settings();
		$s->registerChangeListener("botWorld.run",$this);	
	}

	function onSettingsChange($key,$params) {			
		if ($key=="botWorld.run" && $params["settingsValue"]=="true") {
			$beats=$params["beats"];
			$botArenaId=$params["botArenaId"];
			$this->run($beats,$botArenaId); 
		}
	}

	function settings() {
		return $this->settings;
	}

	function addBotArena(&$botArena) {		
		exit("addBotArena deferred");
/*		if (!is_object($botArena->market()->get())) throw new \Exception("market in bot arena is not object (borrar)");

		$this->botArenas->set($botArena->botArenaId(),$botArena);		
		if (is_int($this->botArenas->get($botArena->botArenaId() ) )) throw new \Exception("botArena cannot be int");*/
	}

	function botArenaIds() {
		return $this->botArenas->keys();
	}

	function botArenaById($botArenaId) {
		if (array_key_exists($botArenaId,$this->botArenas)) {
			return $this->botArenas[$botArenaId];
		}
		Nano\nanoCheck()->checkFailed("botArenaById botArenaId:$botArenaId msg:not found");
	} 

	function trade() {
		foreach($this->traders() as $trader) {
			$trader->trade($this->market());
		}
	}

	function run($beats=1,$botArenaId="",$traderId="",$beatSleep=0,$live=true) {		
		Nano\msg("BotWorld: run beats:$beats botArenaId:$botArenaId botArenaCount:".count($this->botArenas() ));
		Nano\nanoCheck()->checkDiskAvailable();
		
		foreach($this->botArenas() as $botArena) {
				if ($botArenaId!="" && $botArena->botArenaId()!=$botArenaId) continue;
				if ($live && $botArena->market()->useHistory()) continue;
				if (!$live && !$botArena->market()->useHistory()) continue;
				Nano\msg(sprintf("BotWorld: botArenaId: %s run",$botArena->botArenaId() ));
				$botArena->run($beats,$traderId,$beatSleep);
				Nano\msg("BotWorld: -------------------------");
		}
		Nano\msg("BotWorld: end");
	}

	function botArenasAsCsv($live) {	
		$ds=new DataSet\DataSet(["botArenaId","marketId","marketTitle"]);
		$useHistory=!$live;
		foreach($this->botArenas as $botArena) {
			if ($useHistory!=$botArena->market()->useHistory()) continue;
			$ds->addRow([$botArena->botArenaId(),$botArena->market()->marketId(),$botArena->market()->marketTitle()]);
		}

		return $ds->toCsvRet();
	}
}

function run() {
	$s=botWorldBuild();
	$s->run(5000);
}


?>