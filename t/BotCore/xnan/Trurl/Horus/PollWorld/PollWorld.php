<?php
namespace xnan\Trurl\Horus\PollWorld;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Portfolio;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\MarketTrader;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Horus\BotArena;
use xnan\Trurl\Horus\MarketPoll;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Asset\Functions::Load;

class Functions { const Load=1; }

class PollWorld {
	var $pollers=[];

	function __construct() {
	}

	function addPoller(&$poller) {
		$this->pollers[$poller->pollerName()]=$poller;
	}

	function pollerNames() {
		return array_keys($this->pollers);
	}

	function pollerByName($pollerName) {
		foreach($this->pollers as $poller) {
			if ($poller->pollerName()==$pollerName) return $poller;
		}
		Nano\nanoCheck()->checkFailed("pollerByName pollerName:$pollerName msg:not found");
	} 


	function pollQuotes($beats=1,$pollerName="",$beatSleep=0) {
		Nano\msg("PollWorld: pollQuotes beats:$beats pollerName:$pollerName pollerCount:".count($this->pollers));

		foreach($this->pollers as $poller) {
				// ($pollerName!="" && $poller->pollerName()!=$pollerName) continue;
				Nano\msg(sprintf("BotWorld: pollerName: %s run",$poller->pollerName() ));
				$poller->pollQuotes($beats,$beatSleep);
				Nano\msg("PollWorld: -------------------------");
		}
		Nano\msg("PollWorld: end");
	}

}

function pollWorldBuild() {
	$pollWorld=new PollWorld();	
	$p1=new MarketPoll\YahooFinanceMarketPoll("Cryptos");
	$p2=new MarketPoll\YahooFinanceMarketPoll("MervalAccionesLideres");
	$p3=new MarketPoll\YahooFinanceMarketPoll("SnP100");
	$p4=new MarketPoll\YahooFinanceMarketPoll("MervalCedears");
	$p5=new MarketPoll\YahooFinanceMarketPoll("Nasdaq100");
	$p6=new MarketPoll\YahooFinanceMarketPoll("MervalAccionesGeneral");
	$p7=new MarketPoll\YahooFinanceMarketPoll("YFFOREXUSD");

//	$p7=new MarketPoll\YahooFinanceMarketPoll("MervalBonosARP");
//	$p8=new MarketPoll\YahooFinanceMarketPoll("MervalBonosUSD");

	$pollWorld->addPoller($p1);
	$pollWorld->addPoller($p2);	
	$pollWorld->addPoller($p3);	
	$pollWorld->addPoller($p4);	
	$pollWorld->addPoller($p5);	
	$pollWorld->addPoller($p6);	
	$pollWorld->addPoller($p7);	

//	$pollWorld->addPoller($p7);
//	$pollWorld->addPoller($p8);
	return $pollWorld;
}


?>