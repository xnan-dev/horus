<?php
namespace xnan\Trurl\Horus\BotArena;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Portfolio;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\MarketTrader;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Horus\MarketSchedule;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Nano\TextFormatter;
use xnan\Trurl\Horus\BotWorld;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Asset\Functions::Load;

class Functions { const Load=1; }

class BotArena {
	var $traders=[];
	var $botArenaId;
	var $textFormater;	
	var $marketId;
	var $market;

	function __construct($botArenaId,$marketId) {
		srand(0);
		$this->textFormatter=Nano\newTextFormatter();
		$this->botArenaId=$botArenaId;
		$this->marketId=$marketId;		
		$this->setupMarket();		
		$this->setupTraders();
	}

	private function pdo() {
		return BotWorld\BotWorld::instance()->pdo();
	}

	private function setupTraders() {
		$query=sprintf("SELECT botArena.botArenaId,trader.*,dsTrader.* FROM marketTrader as trader
					INNER JOIN botArena as botArena
						ON botArena.botArenaId=trader.botArenaId
					INNER JOIN divideAndScaleMarketTrader as dsTrader 
							ON  trader.traderId=dsTrader.traderId 
								AND trader.botArenaId=dsTrader.botArenaId
								AND trader.botArenaId='%s'",$this->botArenaId);

		$r=$this->pdo()->query($query);

		while ($row=$r->fetch()) {						
			$b=new MarketTrader\DivideAndScaleMarketTrader($row["botArenaId"],$row["traderId"],$row["portfolioId"]);
			$b->setupMarket($this->market());
			$this->traders[]=$b;				
		}		
	}
	
	function setupSettings() {
		$s=BotWorld\BotWorld::instance()->settings();
		$s->registerChangeListener("botArena.reset",$this);	
	}

	function onSettingsChange($key,$params) {			
		if ($params["botArenaId"]==$this->botArenaId()) {			
			if ($key=="botArena.reset" && $params["settingsValue"]=="true") { $this->botArenaReset(); }
		}
	}

	function botArenaReset() {
		exit("botArenaReset: deferred");
		$this->market()->get()->marketReset();

		foreach($this->traders as $trader) {
			$trader->traderReset();
			$trader->portfolio()->portfolioReset();
			$trader->portfolio()->addAssetQuantity($this->market()->get()->defaultExchangeAssetId(),100*1000,$this->market()->get(),true); //deposito inicial.
		}
	}

	function traders() {
		return $this->traders;
	}

	function botArenaId() {
		return $this->botArenaId;
	}

	function addTrader($trader) {		
		exit("addTrader: deferred4");
		if (is_int($trader) || $trader==null ) throw new \Exception("Trader cannot be int");
		$this->traders->insert($trader);
	}

	function firstTrader() {
		return $this->traders[0];
	}

	function traderById($traderId) {
		foreach($this->traders() as $trader) {
			if ($trader->traderId()==$traderId) return $trader;
		}

		Nano\nanoCheck()->checkFailed("traderId: $traderId msg:not found");
	}

	function uniqueTrader() {
		if (count($this->traders())==0) Nano\nanoCheck()->checkFailed("traderUnique: msg: there is no trader");
		if (($this->traders()->count())>1) Nano\nanoCheck()->checkFailed("traderUnique: msg: more than one trader");
		return $this->traders[0];
	}

	function trade($traderId="") {
		foreach($this->traders() as $trader) {

			try {
				if ($traderId!="" && $traderId!=$trader->traderId()) continue;
				if (!$trader->isSuspended()) {	
					$trader->trade($this->market());
					$trader->notifyOrders($this->market());
					$trader->flushOrders($this->market());
				}
			} catch (\Throwable $t) {
				Nano\msg(sprintf("trade: failure: msg exception at trader %s",$trader->traderId()));
				throw $t;
			}
		}		
	}


	function setupMarket() {
		$r=$this->pdo()->query(
			sprintf("SELECT * FROM market m
				INNER JOIN yahooFinanceMarket yf
					ON  m.marketId=yf.marketId
				 WHERE m.marketId LIKE '%s'",$this->marketId));

		$r2=$this->pdo()->query(
			sprintf("SELECT * FROM market m
				INNER JOIN mathMarket mm
					ON  m.marketId=mm.marketId
				 WHERE m.marketId LIKE '%s'",$this->marketId));

		$r3=$this->pdo()->query(
			sprintf("SELECT * FROM market m
				INNER JOIN yahooFinanceTestMarket yf
					ON  m.marketId=yf.marketId
				 WHERE m.marketId LIKE '%s'",$this->marketId));

		$row=$r->fetch();
		if ($row!=null) {
			$market=new Market\yahooFinanceMarket(
				$row["marketId"],$row["assetCount"],false,$row["pollerName"]);

			$market->marketTitle($row["marketTitle"]);
		}

		$row=$r2->fetch();
		if ($row!=null) {			
			$market=new Market\MathMArket(
				$row["marketId"],$row["assetCount"],false);

			$market->marketTitle($row["marketTitle"]);
		}

		$row=$r3->fetch();
		if ($row!=null) {			
			$market=new Market\yahooFinanceTestMarket(
				$row["marketId"],$row["assetCount"],$row["pollerName"]);

			$market->marketTitle($row["marketTitle"]);
		}

		if ($market==null) {
			exit(sprintf("market not found: %s",$this->marketId));
		}

		$this->market=$market;
	}

	function market() {
		return $this->market;
	}

	function marketStatus() {

		foreach($this->market->get()->assetIds() as $assetId) {			
			$marketBeat=$this->market->get()->beat();
			Nano\msg(sprintf("marketBeat:$marketBeat assetId:$assetId quote:%s",
				$this->market->get()->assetQuote($assetId)
			));
		}		
	}

	function tradersStatus() {
			foreach($this->traders() as $trader) {
				$trader->status($this->market);
			}
	}

	function botArenaTradersAsCsv() {	 	
		$header=explode(",","botArenaId,marketId,traderId,traderTitle");
		$ds=new DataSet\DataSet($header);
		foreach($this->traders() as $trader) {
			$ds->addRow([$this->botArenaId(),$this->market()->marketId(),$trader->traderId(),$trader->traderTitle() ]);				
		}
		return $ds->toCsvRet();
	}

	function logMarketStatus() {
		$header[]="marketBeat";
		foreach($this->market->get()->assetIds() as $assetId) {
			$header[]=$assetId;
		}
		$ds=new DataSet\DataSet($header);

		Nano\nanoPerformance()->track("botArena.logMarketStatus");
		$marketBeat=$this->market->beat();			

		foreach($this->market->assetIds() as $assetId) {
			$line[]=$this->market->assetQuote($assetId)->buyQuote();			
		}
		$ds->addRow($line);			
		Nano\nanoPerformance()->track("botArena.logMarketStatus");
		return $ds;
	}

	function logTraderStatus() {
		$header=explode(",","traderId,portfolioValuation");		
		$da=new DataSet\DataSet($header);

		Nano\nanoPerformance()->track("botArena.logTraderStatus");
		$line=array();		
		$marketBeat=$this->market->beat();			
		
		foreach($this->traders()->values() as $trader) {
			$line=array($trader->traderId(),$this->market->portfolioValuation($trader->portfolio));
			$ds->addRow($line);
		}		
		Nano\nanoPerformance()->track("botArena.logTraderStatus");
	}


	function traderPortfolioAsCsv($traderId="") {
		$ds=$this->logTraderPortfolio($traderId);
		return $ds->toCsvRet();
	}

	function botSuggestionsAsCsv($traderId) {
		return ($this->traderById($traderId)->botSuggestionsAsCsv());
	}

	function logTraderPortfolio($traderId="") {

		$header=explode(",","traderId,assetId,assetQuantity,assetQuote,monthRoi,valuation,lastDepositQuantity,lastDepositTime,rowClazz");
		$ds=new DataSet\DataSet($header);

		Nano\nanoPerformance()->track("botArena.logTraderPortfolio");

		$line=array();		
		$ds->deleteRows();
		foreach($this->traders() as $trader) {
			if ($traderId!="" && $trader->traderId!=$traderId) continue;
			$assetIndex=0;
			foreach($trader->portfolio()->assetIds() as $assetId) {
				$portfolioId=$trader->portfolio()->portfolioId();
				$assetQuantity=$trader->portfolio()->assetQuantity($assetId);
				$assetQuote=$this->market->get()->assetQuote($assetId);				

				if ($assetQuantity>0) {				
					$sellQuote=$assetQuote->sellQuote();
					$botArenaId=$this->botArenaId();
					$quantitySaveUrl="settingsKey=portfolio.asset.quantity&assetId=$assetId&portfolioId=$portfolioId&botArenaId=$botArenaId";

					$line=array(
						$assetIndex==0 ? $trader->traderId : "",
						$assetId,						
						$this->textFormatter->formatQuantity($assetQuantity,$quantitySaveUrl),
						$this->textFormatter->formatDecimal($sellQuote,$this->market()->get()->defaultExchangeAssetId()),
						"",
						$this->textFormatter->formatDecimal($assetQuantity*$sellQuote,$this->market()->get()->defaultExchangeAssetId()),						
						$trader->portfolio()->lastDepositQuantity(),
						$trader->portfolio()->lastDepositTime(),
						""
					);	
					$ds->addRow($line);
					++$assetIndex;
				} 
			}			
			$portfolioValuation=$this->market()->portfolioValuation($trader->portfolio());
			$ds->addRow([
				"",
				"Total",
				"",				
				"",
				$this->textFormatter->formatPercent($trader->portfolio()->monthRoi($this->market)),
				$this->textFormatter->formatDecimal($portfolioValuation,$this->market()->defaultExchangeAssetId()),"","","table-warning"]);
		}		

		Nano\nanoPerformance()->track("botArena.logTraderPortfolio");
		return $ds;
	}

	function run($beats,$traderId="",$beatSleep=0) {

		Nano\nanoPerformance()->reset();

		Nano\msg(sprintf("BotArena: botArenaId:%s run",$this->botArenaId));
		Nano\nanoPerformance()->track("botArena.run");		


		Nano\nanoPerformance()->track("botArena.run.A");


		Nano\msg(sprintf("BotArena: botArenaId:%s initial status marketBeat:%s marketClazz:%s beats:%s",$this->botArenaId,$this->market()->beat(), get_class($this->market()),$beats ));		

		$beatsRun=0;

		Nano\nanoPerformance()->track("botArena.run.A");
		Nano\nanoPerformance()->track("botArena.run.B");


		for($beat=0;$beat<$beats;$beat++) {

			if ($this->market()->ready()) {

				$this->trade($traderId);

				$this->market()->nextBeat();				

				if ($beats>1 && $beatSleep>0) { 
					Nano\msg(sprintf("BotArena: run: sleep $beatSleep"));
					usleep(1000000*$beatSleep);
				}

			//$this->logMarketStatus();			

				++$beatsRun;				

			} else {				
				Nano\msg(sprintf("BotArena: botArenaId:%s beat: %s msg: market not ready",$this->botArenaId,$this->market->get()->beat() ));	
			}
		}
		Nano\nanoPerformance()->track("botArena.run.B");
		Nano\nanoPerformance()->track("botArena.run.C");

		//$this->logTraderStatus();			
		$this->logTraderPortfolio();

		if ($beatsRun>0) {
			$this->tradersStatus();
			Nano\nanoPerformance()->track("botArena.run.C");
		}
		Nano\nanoPerformance()->track("botArena.run");
		//Nano\nanoPerformance()->summaryWrite();
	}

	function save() {
		$this->market->save();
		foreach($this->traders()->values() as $trader) {
			$trader->save();
		}
	}

	/*function dehydrate() {
		$this->world=null;
	}

	function hydrate() {
		$this->world=BotWorld::getInstance();
	}*/

	function portfoliosRecover() {
		Nano\msg("BotArena: portfoliosRecover: start");
		foreach($this->traders() as $trader) {
			$trader->portfolio()->portfolioRecover();				
		}		
		Nano\msg("BotArena: portfoliosRecover: end");
	}

	function recover() {
		Nano\msg("BotArena: recover: start");
		foreach($this->traders() as $trader) {
			$trader->traderRecover();				
		}		
		Nano\msg("BotArena: recover: end");
	}
}




?>