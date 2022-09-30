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

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

Asset\Functions::Load;
BotArena\Functions::Load;

class Functions { const Load=1; }

class BotWorld {
	var $botArenas=array();
	var $settings=null;

	function __construct() {
		srand(0);
		$this->settings=new WorldSettings\WorldSettings();
	}

	function settings() {
		return $this->settings;
	}

	function addBotArena(&$botArena) {
		$this->botArenas[$botArena->botArenaId()]=$botArena;
		$botArena->world($this);
	}

	function botArenaIds() {
		return array_keys($this->botArenas);
	}

	function botArenaById($botArenaId) {
		foreach($this->botArenas as $botArena) {
			if ($botArena->botArenaId()==$botArenaId) return $botArena;
		}
		Nano\nanoCheck()->checkFailed("botArenaById botArenaId:$botArenaId msg:not found");
	} 

	function trade() {
		foreach($this->traders as $trader) {
			$trader->trade($this->market);
			Horus\persistence()->afterTradeOne();
		}
	}

	function run($beats=1,$botArenaId="",$traderId="",$beatSleep=0) {
		Nano\msg("BotWorld: run beats:$beats botArenaId:$botArenaId botArenaCount:".count($this->botArenas));

		foreach($this->botArenas as $botArena) {
				if ($botArenaId!="" && $botArena->botArenaId()!=$botArenaId) continue;
				Nano\msg(sprintf("BotWorld: botArenaId: %s run",$botArena->botArenaId() ));
				$botArena->run($beats,$traderId,$beatSleep);
				Nano\msg("BotWorld: -------------------------");
		}
		Nano\msg("BotWorld: end");
	}

	function botArenasAsCsv() {
		$ds=new DataSet\DataSet(["botArenaId","marketId","marketTitle"]);
		
		foreach($this->botArenas as $botArena) {
			$ds->addRow([$botArena->botArenaId(),$botArena->market()->marketId(),$botArena->market()->marketTitle()]);
		}

		return $ds->toCsvRet();
	}

}

function botWorldBuild() {
	$botWorld=new BotWorld();
	$mathArena=BotArena\mathArenaBuild("mathArena");
	
	//$cryptosHistoryArena=BotArena\yahooFinanceArenaBuild("cryptosHistoryArena","Cryptos Históricas","botHistory","Cryptos en Vivo",15,true,"Cryptos");		
	
	$cryptosLiveArena=BotArena\yahooFinanceArenaBuild("cryptosLiveArena","Cryptos","botLive","Bot Cryptos en Vivo",15,false,"Cryptos","USD");

	$mervalAccionesGeneralLiveArena=BotArena\yahooFinanceArenaBuild("mervalAccionesGeneralLiveArena","Merval Acciones Generales","botLive","Bot Merval Generales en Vivo",15,false,"MervalAccionesGeneral","AR$");
	
	$mervalAccionesLideresLiveArena=BotArena\yahooFinanceArenaBuild("mervalAccionesLideresLiveArena","Merval Acciones Líderes","botLive","Bot Líderes en Vivo",15,false,"MervalAccionesLideres","AR$");
	
	$mervalCedearsLiveArena=BotArena\yahooFinanceArenaBuild("mervalCedearsLiveArena","Merval Cedears","botLive","Bot Cedears en Vivo",15,false,"MervalCedears","AR$");
	
	$nasdaq100liveArena=BotArena\yahooFinanceArenaBuild("nasdaq100liveArena","Nasdaq 100","botLive","Bot Nasdaq 100 en Vivo",15,false,"Nasdaq100","USD");
	
	$snP100liveArena=BotArena\yahooFinanceArenaBuild("snP100liveArena","S&P 100","botLive","Bot S&P 100 en Vivo",15,false,"SnP100","USD");

	$botWorld->addBotArena($mathArena);
	//$botWorld->addBotArena($cryptosHistoryArena);
	$botWorld->addBotArena($cryptosLiveArena);

	$botWorld->addBotArena($mervalAccionesGeneralLiveArena);
	$botWorld->addBotArena($mervalAccionesLideresLiveArena);
	$botWorld->addBotArena($mervalCedearsLiveArena);
	$botWorld->addBotArena($nasdaq100liveArena);
	$botWorld->addBotArena($snP100liveArena);

	return $botWorld;
}

function run() {
	$s=botWorldBuild();
	$s->run(5000);
}


?>