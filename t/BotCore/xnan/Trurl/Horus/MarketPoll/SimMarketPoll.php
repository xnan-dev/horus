<?php
namespace xnan\Trurl\Horus\MarketPoll;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Nano\DataSet;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

class SimMarketPoll {
	var $market;
	var $dsMarketStatus;
	var $dsMarketQuotes;
	
	function __construct() {
		$this->market=Trurl\sessionGet("SimMarketPollMarket");
		if ($this->market==null) {
			$this->market=new Market\Market("SimMarket",5);
			Trurl\sessionSet("SimMarketPollMarket",$this->market);		
		}
	}

	function setupMarketStatusDataSet() {
		$header[]="marketBeat";
		foreach($this->market->assetIds() as $assetId) {
			$header[]=$assetId;
		}
		$this->dsMarketStatus=new DataSet\DataSet($header);
	}

	function setupMarketQuoteDataSet() {
		$header=explode(",","assetId,buyQuote");		
		$this->dsMarketQuotes=new DataSet\DataSet($header);
	}

	function logMarketStatus() {
		$line=array();
		$marketBeat=$this->market->beat();			
		$line[]=$marketBeat;
		foreach($this->market->assetIds() as $assetId) {
			$line[]=$this->market->assetQuote($assetId)->buyQuote();			
		}
		$this->dsMarketStatus->addRow($line);			
	}

	function logMarketQuotes() {	
		$marketBeat=$this->market->beat();					
		foreach($this->market->assetIds() as $assetId) {
			$line=array($assetId,$this->market->assetQuote($assetId)->buyQuote());
			$this->dsMarketQuotes->addRow($line);			
		}		
	}

 	function pollQuotes() {
 		Nano\msg(sprintf("SimMarketPoll: poll time:%s marketBeat:%s",time(),$this->market->beat() ));
 		$this->setupMarketStatusDataSet();
 		$this->setupMarketQuoteDataSet();
 		$this->logMarketStatus();
 		$this->logMarketQuotes();
 		$folder=sprintf("content/poll/%s",$this->market->marketId());
 		if (!file_exists($folder)) mkdir($folder);
 		$this->dsMarketStatus->toCsv("$folder/marketStatus.csv",true);
 		$this->dsMarketQuotes->toCsv("$folder/marketQuotes.csv",false);
 		$this->market->nextBeat();
 	}
}

?>