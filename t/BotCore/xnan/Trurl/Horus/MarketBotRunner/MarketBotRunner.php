<?php
namespace xnan\Trurl\Horus\MarketBotRunner;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Mycro: Main
use xnan\Trurl\Mikro\ServiceQuery;
use xnan\Trurl\Mikro\RestService;

// Uses:  Horus: Shortcuts
use xnan\Trurl\Horus;
Horus\Functions::Load;

//Uses: Custom
use xnan\Trurl\Horus\Builders;
Builders\Functions::Load;

use xnan\Trurl\Horus\PdoSettings;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Horus\Persistence;

//Uses: End

class MarketBotRunner extends RestService\RestService {
	var $pdoSettings;
	static $instance;

	static function instance() {
		if (MarketBotRunner::$instance==null)  {			
			MarketBotRunner::$instance=new MarketBotRunner();
			// MarketBotRunner::$instance->botWorld=Builders\botWorldBuild();
		}
		return MarketBotRunner::$instance;
	}	

	private function pdoConnect() {
		$this->pdo = new \PDO(
		    sprintf('mysql:host=%s;dbname=%s',
		    	$this->pdoSettings->hostname(),
		    	$this->pdoSettings->database()),
			    $this->pdoSettings->user(),
			    $this->pdoSettings->password());		

		$this->botWorld()->pdo($this->pdo);		
		$this->botWorld()->persistence()->pdoSettings($this->pdoSettings);		
		$this->botWorld()->persistence()->pdo($this->pdo);		
		$this->botWorld()->afterPdoSetup();		
	}

	private function pdo() {
		return $this->pdo;
	}

	function botWorld() {
		return BotWorld\BotWorld::instance();
	}

	function pdoSettings($pdoSettings=null) {
		if ($pdoSettings!=null) {
			$this->pdoSettings=$pdoSettings;	
			$this->pdoConnect();
		}
		return $this->pdoSettings;
	}

	function kill() {
		throw new \Exception("unsupported");
		parent::kill();
		//(HRefs\HRefs::instance())->kill();
		//(HMaps\HMaps::instance())->kill();
		exit("runner killed");
	}

	function prmQ() {
		return $this->param("q");		
	}

	function prmBotArenaId() {
		return $this->param("botArenaId","");
	}

	function prmTraderId() {
		return $this->param("traderId","");
	}

	function prmXcount() {
		return $this->param("xcount",100);
	}
	
	function prmLive() {
		return $this->param("live","true")=="true";
	}

	function prmQueueId() {
		return $this->param("queueId","1");
	}


	function prmSettingsKey() {
		return $this->param("settingsKey","");
	}

	function prmBeats() {
		return $this->param("beats",1);
	}

	function prmBeatSleep() {
		return $this->param("beatSleep",0);
	}

	function srvBotArenasAsCsv() {
		return $this->botWorld()->botArenasAsCsv($this->prmLive());
	}

	function srvBotArenaTradersAsCsv() {
		return $this->botWorld()->botArenaById($this->prmBotArenaId())->botArenaTradersAsCsv();
	}

	function srvTraderPortfolioAsCsv() {
		return $this->botWorld()
			->botArenaById($this->prmBotArenaId())
			->traderPortfolioAsCsv($this->prmTraderId());		
	}


	function srvBotSuggestionsAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();
		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		
		return $this->botWorld()->botArenaById($botArenaId)->botSuggestionsAsCsv($traderId);		
	}
	
	function srvTraderQueuePendingAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->queuePendingAsCsv();
	}

	function srvTraderQueueDoneAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->queueDoneAsCsv();
	}

	function srvTraderQueueCancelledAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->queueCancelledAsCsv();
	}

	function srvMarketSettingsAsCsv() {
		return $this->botWorld()->botArenaById($this->prmBotArenaId())->market()->settingsAsCsv();
	}

	function srvTraderSettingsAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->traderSettingsAsCsv();
	}

	function srvTraderStatsAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->statsAsCsv();
	}

	function srvTraderStatsMediumAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->statsMediumAsCsv();
	}

	function srvTraderStatsLongAsCsv() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->statsLongAsCsv();
	}

	function srvMarketScheduleAsCsv() {
		return $this->botWorld()->botArenaById($this->prmBotArenaId())->market()->marketSchedule()->scheduleSettingsAsCsv();
	}

	function srvTraderSettingsCsv() {
		return $this->traderSettingsCsv($this->prmBotArenaId(),$this->prmTraderId());
	}

	function srvMarketQuotes() {
		return $this->botWorld()->botArenaById($this->prmBotArenaId())->market()->quotesAsCsv();
	}
	
	function srvMarketLastQuotesAsCsv() {
		return $this->botWorld()->botArenaById($this->prmBotArenaId())->market()->lastQuotesAsCsv();
	}

	function srvTradeOpAccept() {	
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->acceptOrder($this->prmQueueId());
	}
	
	function srvTraderSuspend() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->suspend();
	}

	function  srvTraderResume() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->resume();	
	}
	
	function  srvTradeOpCancel() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->cancelOrder($this->prmQueueId());
	}

	function srvRun() {
		header("Content-Type: text/plain");		
		try {
			$this->pdo()->beginTransaction();

			Nano\nanoPerformance()->track("runner.run");
			$this->botWorld()->run($this->prmBeats(),$this->prmBotArenaId(),$this->prmTraderId(),$this->prmBeatSleep(),$this->prmLive());
			Nano\nanoPerformance()->track("runner.run");

			$this->pdo()->commit();

		} catch (\Exception $e) {
			$this->pdo()->rollback();			
			Nano\nanoLog()->msg("srvRun: failed: ".$e->getMessage());
		}
	}

	function srvWorldSettingsChange() {
		return $this->botWorld()->settings()->settingsChange($this->prmSettingsKey(),$_GET);
	}

	function  srvKill() {
		$this->kill();
	}

	function srvMarketHistoryAsJson() {
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->marketStats()->get()->marketHistoryAsJson($this->prmXcount());
	}

	function srvTraderHistoryAsJson() {		
		$traderId=$this->prmTraderId();
		$botArenaId=$this->prmBotArenaId();

		if ($traderId=="") $traderId=$this->botWorld()->botArenaById($botArenaId)->uniqueTrader()->traderId();
		return $this->botWorld()->botArenaById($botArenaId)->traderById($traderId)->marketStats()->get()->marketHistoryAsJson($this->prmXcount());
	}

	function setupServiceQueries() {
		$timeoutShort="\\xnan\\Trurl\\serviceTimeoutShort";
		$timeoutLong="\\xnan\\Trurl\\serviceTimeoutLong";

		$this->registerServiceGroup("markets","Markets");		
		$this->registerServiceQuery("markets","botArenaAsCsv","q=botArenasAsCsv&live=true","csv",$timeoutShort);
		$this->registerServiceQuery("markets","marketSettingsAsCsv","q=marketSettingsAsCsv&botArenaId={botArenaId}","csv",$timeoutShort);
		$this->registerServiceQuery("markets","marketScheduleAsCsv","q=marketScheduleAsCsv&botArenaId={botArenaId}","csv",$timeoutShort);
		$this->registerServiceQuery("markets","marketQuotes","q=marketQuotes&botArenaId={botArenaId}","csv",$timeoutLong);
		$this->registerServiceQuery("markets","marketLastQuotesAsCsv","q=marketLastQuotesAsCsv&botArenaId={botArenaId}",$timeoutShort);

		$this->registerServiceGroup("bots","Bots");
		$this->registerServiceQuery("bots","botArenaTradersAsCsv","q=botArenaTradersAsCsv&botArenaId={botArenaId}","csv",$timeoutShort);
		$this->registerServiceQuery("bots","botSuggestionsAsCsv","q=botSuggestionsAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);
		$this->registerServiceQuery("bots","traderPortfolioAsCsv","q=traderPortfolioAsCsv&botArenaId={botArenaId}","csv",$timeoutShort);
		$this->registerServiceQuery("bots","tradeOpCancel","q=tradeOpCancel&botArenaId={botArenaId}&traderId={traderId}&queueId={queueId}","csv",$timeoutShort);
		$this->registerServiceQuery("bots","tradeOpAccept","q=tradeOpAccept&botArenaId={botArenaId}&traderId={traderId}&queueId={queueId}","csv",$timeoutShort);
		$this->registerServiceQuery("bots","traderSuspend","q=traderSuspend&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);
		$this->registerServiceQuery("bots","traderResume","q=traderResume&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);
		$this->registerServiceQuery("bots","traderSettingsAsCsv","q=traderSettingsAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);

		$this->registerServiceGroup("botQueues","Bot Queues");
		$this->registerServiceQuery("botQueues","traderQueuePendingAsCsv","q=traderQueuePendingAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);
		$this->registerServiceQuery("botQueues","traderQueueDoneAsCsv","q=traderQueueDoneAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);
		$this->registerServiceQuery("botQueues","traderQueueCancelledCsv","q=traderQueueDoneAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);

		$this->registerServiceQuery("botQueues","traderStatsAsCsv","q=traderStatsAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);

		$this->registerServiceQuery("botQueues","traderStatsMediumAsCsv","q=traderStatsMediumAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);

		$this->registerServiceQuery("botQueues","traderStatsLongAsCsv","q=traderStatsLongAsCsv&botArenaId={botArenaId}&traderId={traderId}","csv",$timeoutShort);

		$this->registerServiceGroup("botRuns","Bot Runs");
		$this->registerServiceQuery("botRuns","run","beats={beats}&botArenaId={botArenaId}&q=run","log",$timeoutLong);
		$this->registerServiceQuery("botRuns","runx20","beats=20&botArenaId={botArenaId}&q=run","log",$timeoutLong);
		$this->registerServiceQuery("botRuns","runx100","beats=100&q=run","log",$timeoutLong);
	}

	function serviceQueryToUrl($query) {
		return sprintf("%s?%s",Horus\marketBotRunnerWebUrl(),$query);
	}

	function help() {
		print("<PRE>");			
		
		$this->htmlTitle(sprintf("MarketBotRunner %s",Horus\marketBotRunnerVersion()));				
		Nano\msg(sprintf("restServiceId:%s",$this->restServiceId()));		
		Nano\msg(sprintf("dateCreated:%s",Nano\nanoTextFormatter()->dateLegend($this->timeCreated)));	
		

		$this->htmlTitle("General");	
		Nano\msg($this->htmlLink("Bot Arena Web","$domain/BotArenaWeb/index.php"));
		Nano\msg($this->htmlLink("Bot Runner Tester","$domain/MarketBotRunnerWeb/tester.php"));
		Nano\msg($this->htmlLink("Market Poll Tester","$domain/MarketPollWeb/tester.php"));
		Nano\msg($this->htmlLink("Market Poll main","$domain/MarketPollWeb/index.php"));		
		Nano\msg($this->htmlLink("kill","$domain/MarketBotRunnerWeb/index.php?q=killDISABLED"));
  		
		parent::help();

		$this->htmlTitle("Tests");
		Nano\msg($this->htmlLink("sanityTest","$domain/MarketBotRunnerWeb/sanityTest.php"));

		$this->htmlTitle("Settings");
		foreach($this->botWorld()->settings()->settingsChangeKeys() as $settingsKey) {
			Nano\msg($this->htmlLink("$settingsKey","$domain/MarketBotRunnerWeb/index.php?q=worldSettingsChangeDISABLED&settingsKey=$settingsKey&settingsValue=VALUE"));
		}


		print("</PRE>");
	}

}

?>