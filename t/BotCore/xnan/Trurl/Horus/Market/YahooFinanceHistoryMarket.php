<?php
namespace xnan\Trurl\Horus\Market;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Nano\DataSet;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

Trurl\Functions::Load;
Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

set_time_limit(120*10);

class YahooFinanceHistoryMarket extends AbstractMarket {
	var $marketHistory=array();
	var $historyPage=0;
	var $historyPageSize=20;

	var $lastQueryBeat=0;
	var $lastQueryIndex=0;	

	var $quotesCache=null;
	var $beat=0;
	var $lastHistoryByAsset=null;

	function __construct($marketId,$assetCount) {
		$tradeFixedFees=array(1.5);
		$assetsCsv = $this->callAssets();
		$index=0;
		$assets=array();
		foreach($assetsCsv as $assetCsv) {
			if ($index>=$assetCount) break;
			$assets[]=new Asset\Asset($assetCsv["assetId"]);
			++$index;
		} 
		$assets[]=new Asset\Asset("USD",AssetType\Currency);


		$this->fillHistoryCache();

		parent::__construct($marketId,$assets,$tradeFixedFees,true);
	}

	function fillHistoryCache() {
		$outOfPage=$this->beat<$this->historyPage*$this->historyPageSize || 
			$this->beat>$this->historyPage*$this->historyPageSize+$this->historyPageSize-1;

		if ($this->marketHistory==null || $outOfPage)  {
			if ($outOfPage) $this->historyPage=floor($this->beat/$this->historyPageSize);
			$this->marketHistory=$this->callMarketHistory($this->historyPage,$this->historyPageSize);
		}				
	}

	function addCustomSettings($ds) {
		$ds->addRow(["lastQueryBeat","Pulso de última actualización",$this->lastQueryBeat]);
		$ds->addRow(["lastQueryIndex","Indice de última actualización",$this->lastQueryIndex]);
		$ds->addRow(["useHistory","Usar cotizaciones históricas","true"]);
		$ds->addRow(["beat","Pulso de cotización histórica",$this->beat]);
	}
	
	function marketPollWebUrl() {
		return sprintf("%s/MarketPollWeb",Trurl\domain());

	}

	function quotesAsCsv() {
		return file_get_contents(sprintf("%s?q=marketQuotesAsCsv",$this->marketPollWebUrl()));
	}

	function lastQuotesAsCsv() {
		$marketLastQuotes=$this->marketLastQuotes();
		$header= array("marketBeat","assetId","buyQuote");
		$dsMarketQuotes=new DataSet\DataSet($header);
		foreach ($marketLastQuotes as $assetId=>$quote) {
			$dsMarketQuotes->addRow(array(
				$this->beat,
				$assetId,
				$this->textFormatter()->formatDecimal($quote->buyQuote(),$quote->fromAssetId()
			 )));
		}		
		return  $dsMarketQuotes->toCsvRet();			
	}

	function marketQuotes() {
		$url=sprintf("%s/index.php?q=marketQuotesAsCsv",$this->marketPollWebUrl());
		return Nano\nanoCsv()->csvContentToArray(file_get_contents($url),';');
	}

	function callMarketHistory($page,$pageSize) {
		$url=sprintf("%s/index.php?q=marketHistoryAsCsv&page=%s&pageSize=%s",$this->marketPollWebUrl(),$page,$pageSize);		
		return Nano\nanoCsv()->csvContentToArray(file_get_contents($url),';');
	}

	function callAssets() {
		return Nano\nanoCsv()->csvContentToArray($this->assetsAsCsv(),';');
	}

	function assetsAsCsv() {
		return file_get_contents(sprintf("%s?q=assetsAsCsv",$this->marketPollWebUrl()));
	}

	function marketLastQuotes() {
		$ret=$this->lastHistoryByAsset;	
		
		if ($this->lastHistoryByAsset!=null) {
			$ret=$this->lastHistoryByAsset;	
		}  else {
			$this->assetQuote($this->assetIds[0]); // fuerza carga de cache.
			$ret=$this->lastHistoryByAsset;
		}
		return $ret;
	}

	function fillLastHistoryByAsset() {
		if ($this->lastHistoryByAsset!=null) return;

		$newQueryIndex=$this->lastQueryBeat<=$this->beat() ? $this->lastQueryBeat:0; // to avoid re-scan the full table each time.			

		$this->fillHistoryCache();
		
		for($i=0;$i<count($this->marketHistory);$i++) {
			//if ($this->beat()>$this->lastQueryBeat) { 
			//	$this->lastQueryBeat=$this->beat();
			//}
			//$this->lastQueryIndex=$i;

			$historyEntry=$this->marketHistory[$i];
						
			$entryBeat=$historyEntry["marketBeat"];

			if ($entryBeat==$this->beat()) {
				$cacheAssetId=$historyEntry["assetId"];
				$cacheSellQuote=($historyEntry["high"]+$historyEntry["low"])/2;
				$cacheBuyQuote=$cacheSellQuote;
				$cacheQuote=new AssetQuotation\AssetQuotation("USD",$cacheAssetId,$cacheSellQuote,$cacheBuyQuote);
				$this->lastHistoryByAsset[$cacheAssetId]=$cacheQuote;
				//Trurl\msgPerformance("assetQuote:end");									
			} else if ($entryBeat>$this->beat()) {
				break;
			}
		}		
	}
	
	function assetQuote($assetId):AssetQuotation\AssetQuotation {

		//Trurl\msgPerformance("assetQuote:start");
		if  ($assetId=="USD" || $this->lastHistoryByAsset==null) return new AssetQuotation\AssetQuotation($assetId,"USD", 1, 1); // null check: fast patch. TODO corregir/baja de clase
		

		$ret=new AssetQuotation\AssetQuotation($assetId,"USD",0,0); 
	
		$this->fillLastHistoryByAsset();

		$quote=array_key_exists($assetId,$this->lastHistoryByAsset) ? $this->lastHistoryByAsset[$assetId] : null;
		if ($quote!=null) $ret=$quote;

		//Nano\nanoCheck()->checkFailed("assetQuote: assetId:$assetId msg:not found");		
		return $ret;
	}

	function assetLastQuote($assetId):AssetQuotation\AssetQuotation {		
		$quotes=$this->marketLastQuotes();
		if  ($assetId=="USD") return new AssetQuotation\AssetQuotation($assetId,"USD", 1, 1);

		foreach ($quotes as $quote) {
			if ($quote["assetId"]==$assetId) {
				return new AssetQuotation\AssetQuotation($assetId,"USD",$quote["sellQuote"],$quote["buyQuote"]);	
			}
		}
		Nano\nanoCheck()->checkFailed("assetLastQuote assetId:$assetId msg:not found");
	}

	function beat() {		
		return $this->beat;			
	}

	function ready() {
		return true;		
	}

	function nextBeat() {		
		++$this->beat;
		$this->quotesCache=null;
		$this->lastHistoryByAsset=null;

		$this->onBeat()->observeAll($this);		
	}
}

?>