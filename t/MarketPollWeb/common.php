<?php
namespace xnan\MarketPollWeb;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Hydra: Shortcuts
use xnan\Trurl\Hydra;
Hydra\Functions::Load;

// Uses: Horus: Shortcuts
use xnan\Trurl\Horus;
Horus\Functions::Load;

// Uses: End

use xnan\Trurl;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\MarketSimulator;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\MarketPoll;
use xnan\Trurl\Horus\PollWorld;

chdir( __DIR__ );

require("autoloader.php");
require '../vendor/autoload.php';
include_once("settings.php");

Trurl\Functions::Load;
PollWorld\Functions::Load;

function timeZone() {
	return 'America/Argentina/Buenos_Aires';
}

class MarketPollRunner {
	var $pollWorld;
	var $pdoSettings;
	var $pdo;

	function __construct() {		
		$this->pollWorld=PollWorld\pollWorldBuild();
	}

	private function pdoConnect() {
		$this->pdo = new \PDO(
		    sprintf('mysql:host=%s;dbname=%s',
		    	$this->pdoSettings->hostname(),
		    	$this->pdoSettings->database()),
			    $this->pdoSettings->user(),
			    $this->pdoSettings->password());
	}

	private function pdo() {
		return $this->pdo;
	}

	function pdoSettings($pdoSettings=null) {
		if ($pdoSettings!=null) {
			$this->pdoSettings=$pdoSettings;	
			$this->pdoConnect();
			$this->pollWorld->pdo($this->pdo);
		}
		return $this->pdoSettings;
	}

	function pollWorld() {
		return $this->pollWorld;
	}

	function marketPollQuotes($beats,$pollerName,$beatSleep) {
		try {
			$this->pdo()->beginTransaction();
			$w=$this->pollWorld();
			$w->pollQuotes($beats,$pollerName,$beatSleep);
			$this->pdo()->commit();
		} catch(\Exception $e) {
			$this->pdo()->rollback();
			Nano\nanoCheck()->checkFailed("marketPollQuotes: msg:".$e->getMessage());
		}
	}

	 function marketPollHistory($pollerName) {
		$w=$this->pollWorld();
		$w->pollerByName($pollerName)->pollHistory();	
	}

	function assetsAsCsv($pollerName) {
		$w=$this->pollWorld();
		print $w->pollerByName($pollerName)->assetsAsCsv();	
	}

	function marketQuotesAsCsv($pollerName) {
		$w=$this->pollWorld();
		print $w->pollerByName($pollerName)->marketQuotesAsCsv();	
	}

	function marketHistoryAsCsv($pollerName) {
		$w=$this->pollWorld();
		$page=param("page",0);
		$pageSize=param("pageSize",20);
		print $w->pollerByName($pollerName)->marketHistoryAsCsv($page,$pageSize);	
	}

	function marketLastQuotesAsCsv($pollerName) {
		$w=$this->pollWorld();
		print $w->pollerByName($pollerName)->marketLastQuotesAsCsv();		
	}

}


function param($key,$default="") {
	if (array_key_exists($key,$_GET)) return $_GET[$key];
	return $default;
}

function my_link($title=null,$url) {
	if ($title==null) $title=$url;
	return sprintf('<a href="%s">%s</a>',$url,$title);
}

function my_title($title) {
	Nano\msg("");
	Nano\msg("<b>$title</b>");
}


Nano\nanoLog()->open();

$runner=new MarketPollRunner();
$runner->pdoSettings(pdoSettings());

date_default_timezone_set(timeZone());

Nano\nanoLog()->open();

if (param("q","")=="assetsAsCsv") {
		header('Content-Type:text/plain');
		$pollerName=param("pollerName","Cryptos");
		$runner->assetsAsCsv($pollerName);
} else if (param("q","")=="marketQuotesAsCsv") {
		header('Content-Type:text/plain');
		$pollerName=param("pollerName","Cryptos");
		$runner->marketQuotesAsCsv($pollerName);
} else if (param("q","")=="marketLastQuotesAsCsv") {
		header('Content-Type:text/plain');
		$pollerName=param("pollerName","Cryptos");
		$runner->marketLastQuotesAsCsv($pollerName);
} else if (param("q","")=="marketHistoryAsCsv") {
		header('Content-Type:text/plain');
		$pollerName=param("pollerName","Cryptos");
		$runner->marketHistoryAsCsv($pollerName);
} else if (param("q","")=="pollQuotes") {

	Nano\nanoPerformance()->track("pollQuotes");

	$beats=param("beats",1);
	$beatSleep=param("beatSleep",0);
	$pollerName=param("pollerName","");
	header('Content-Type:text/plain');
	$runner->marketPollQuotes($beats,$pollerName,$beatSleep);

	Nano\nanoPerformance()->track("runner.pollQuotes");

	if (showPerformance()) Nano\nanoPerformance()->summaryWrite();

} else if (param("q","")=="pollHistory") {
	header('Content-Type:text/plain');
	$pollerName=param("pollerName","Cryptos");
	$runner->marketPollHistory($pollerName);
} else {
	print("<PRE>");
	$domain=Horus\domain();

	my_title("Help");
	Nano\msg(sprintf("sessionId:%s",session_id()));
	Nano\msg(sprintf("q:%s",param("q")));

	my_title("General");
	Nano\msg(sprintf(my_link("Poll Tester","$domain/MarketPollWeb/tester.php")));
	Nano\msg(sprintf(my_link("Bot Runner Tester","$domain/MarketBotRunnerWeb/tester.php")));
	Nano\msg(sprintf(my_link("Bot Runner main","$domain/MarketBotRunnerWeb/index	.php")));

	my_title("Consultas");
	Nano\msg(sprintf(my_link("assetsAsCsv","$domain/MarketPollWeb/index.php?q=assetsAsCsv")));
	Nano\msg(sprintf(my_link("marketQuotesAsCsv","$domain/MarketPollWeb/index.php?q=marketQuotesAsCsv")));
	Nano\msg(sprintf(my_link("marketLastQuotesAsCsv","$domain/MarketPollWeb/index.php?q=marketLastQuotesAsCsv")));
	Nano\msg(sprintf(my_link("marketHistoryAsCsv","$domain/MarketPollWeb/index.php?q=marketHistoryAsCsv&page=0&pageSize=20")));

	my_title("Polling");
	Nano\msg(sprintf(my_link("pollQuotes","$domain/MarketPollWeb/index.php?q=pollQuotes&beats=1&beatSleep=0")));
	Nano\msg(sprintf(my_link("pollHistory","$domain/MarketPollWeb/index.php?q=pollHistoryDISABLED")));
	print("</PRE>");

}

Nano\nanoLog()->close();

?>