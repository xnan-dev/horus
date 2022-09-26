<?php
namespace xnan\Trurl\Horus\Market;
use xnan\Trurl;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Horus\BotWorld;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Horus: Shortcuts
use xnan\Trurl\Horus;
Horus\Functions::Load;

//Uses: End

Trurl\Functions::Load;
Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

set_time_limit(120*10);

class YahooFinanceMarket extends AbstractMarket {
	var $lastQuotesCache=null;
	var $assets=[]; // cached
	var $assetIds=[]; // cached
	var $assetIdsByType=[]; // cached
	var $tradeFixedFees=[]; // TODO impl.
	
	function __construct($marketId,$pollerName="Cryptos") {		
		$this->pollerName=$pollerName;		
		parent::__construct($marketId,false);
	}

	protected function setupMarket() {
		parent::setupMarket();
		$this->setupAssets();
	}

	private function setupAssets() {
		$assetsCsv = Nano\nanoCsv()->csvContentToArray($this->assetsAsCsv(),';');
				
		$index=0;
		foreach($assetsCsv as $assetCsv) {
			//if ($index>=$assetCount) break;
			$asset=new Asset\Asset($assetCsv["assetId"]);
			$this->assets[]=$asset;
			$this->assetIds[]=$asset->assetId();
			$this->assetIdsByType[AssetType\CryptoCurrency]=$asset->assetId();
			++$index;
		} 				

		$defaultAssetId=$this->defaultExchangeAssetId();
		$this->assets[]=new Asset\Asset($defaultAssetId,AssetType\Currency);
		$this->assetIds[]=$defaultAssetId;
		$this->assetIdsByType[AssetType\Currency]=$defaultAssetId;
	}


	function lastBeatRead($lastBeatRead=null) {
		return Horus\persistence()->yfMarketLastBeatRead($this->marketId(),$lastBeatRead);
	}

	function assets() {		
		return $this->assets;
	}

	function assetIds() {
		return $this->assetIds;
	}

	function assetIdsByType($asseType) {
		return $this->assetIdsByType;
	}

	function tradeFixedFees() {
		return $this->tradeFixedFees;
	}	

	function pollerName() {
		return $this->pollerName;
	}

	function addCustomSettings($ds) {		
		$ds->addRow(["useHistory","Usar cotizaciones históricas",$this->useHistory() ? "true":"false"]);
		$ds->addRow(["pollerName","Nombre de poller asociado",$this->pollerName() ]);
		$ds->addRow(["lastBeatRead","Último pulso procesado",$this->lastBeatRead() ]);		
	}
	
	function quotesAsCsv() {		
		return file_get_contents(Horus\marketPollerQuery("q=marketQuotesAsCsv",$this->pollerName()) );
	}	

	function lastQuotesAsCsv() {
		if ($this->lastQuotesCache==null) $this->setupLastQuotesCache();
		//$this->pollerQuery("q=marketLastQuotesAsCsv");		
		//return file_get_contents($this->pollerQuery("q=marketLastQuotesAsCsv"));		
		
		$rows=$this->marketLastQuotes();

		$header=explode(",","marketBeat,assetId,buyQuote,sellQuote,reportedDate,pollDate");
		$ds=new DataSet\DataSet($header);
		foreach($rows as $r) {
			$quote=$this->assetLastQuote($r["assetId"]);

			$ds->addRow([
					$r["marketBeat"],
					$this->textFormatter()->formatLink($r["assetId"],$this->assetPollSourceUrl($r["assetId"])),
					$this->textFormatter()->formatDecimal($r["buyQuote"],$quote->toAssetId(),""),
					$this->textFormatter()->formatDecimal($r["sellQuote"],$quote->toAssetId(),""),
					$r["reportedDate"],
					$r["pollDate"]
				]);				
		}
		return $ds->toCsvRet();
	}

	function assetsAsCsv() {		
		return Horus\callServiceMarketPollerCsv("q=assetsAsCsv",$this->pollerName());
	}

	function marketHistory() {		
		return Horus\callServiceMarketPollerArray("q=marketHistoryAsCsv",$this->pollerName());
	}

	function marketQuotes() {
		return Horus\callServiceMarketPollerArray("q=marketQuotesAsCsv",$this->pollerName());
	}

	function setupLastQuotesCache() {
		$ret=Horus\callServiceMarketPollerArray("q=marketLastQuotesAsCsv",$this->pollerName());
		$this->lastQuotesCache=$ret;
		return $ret;
	}

	function marketLastQuotes() {
		return $this->lastQuotesCache;
	}

	function assetLastQuote($assetId):AssetQuotation\AssetQuotation {		
		if ($this->lastQuotesCache==null) $this->setupLastQuotesCache();

		$quotes=$this->lastQuotesCache;
		if  ($assetId==$this->defaultExchangeAssetId())
			 return new AssetQuotation\AssetQuotation($assetId,$this->defaultExchangeAssetId(), 1, 1);		

		foreach ($quotes as $quote) {
			if ($quote["assetId"]==$assetId) {
				return new AssetQuotation\AssetQuotation($assetId,
					$this->defaultExchangeAssetId(),$quote["sellQuote"],$quote["buyQuote"]);	
			}
		}
		return new AssetQuotation\AssetQuotation($assetId,$this->defaultExchangeAssetId(), 0, 0);
		//Nano\nanoCheck()->checkFailed("assetLastQuote assetId:$assetId msg:not found");
	}

	function assetQuote($assetId):AssetQuotation\AssetQuotation {
		Nano\nanoPerformance()->track("yfMarket.assetQuote");
		$ret=$this->assetLastQuote($assetId);			
		Nano\nanoPerformance()->track("yfMarket.assetQuote");
		return $ret;
	}

	function beat($beat=null) {		
		if ($beat!=null) throw \Exception("unsupported for yfMarket");
		$lastQuotes=$this->marketLastQuotes();		
		if ($lastQuotes==null) return 0;		
		return $lastQuotes[0]["marketBeat"];
	}

	function assetPollSourceUrl($assetId) {
		return sprintf("https://finance.yahoo.com/quote/%s?p=%s",$assetId,$assetId);
	}

	function ready() {
		$this->lastQuotesCache=null;

		$this->setupLastQuotesCache();
		$ret=$this->lastBeatRead()<$this->beat();
		return $ret;
	}

	function nextBeat() {		
		$this->lastBeatRead($this->beat());
		$this->lastQuotesCache=null;
		Nano\nanoLog()->msg(printf("lastRead:%s\n",$this->lastBeatRead() ));
		Nano\nanoPerformance()->track("yfMarket.observeAll");
		$this->onBeat()->observeAll($this);		
		Nano\nanoPerformance()->track("yfMarket.observeAll");
	}

	function dehydrate() {
		parent::dehydrate();
		$this->lastQuotesCache=null;
//		print "<br>YYMARKET DEHYDRATED.".$this->marketId()."<br>";
	}

	function hydrate() {
		parent::hydrate();
	}
}

?>