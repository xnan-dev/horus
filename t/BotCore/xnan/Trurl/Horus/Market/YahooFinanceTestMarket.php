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

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Trurl\Functions::Load;
Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

set_time_limit(120*10);

class YahooFinanceTestMarket extends AbstractMarket {
	var $quotesCache=null;
	var $cacheIndex=0;
	var $cacheBeat=-1;
	var $pollerName="";

	function __construct($marketId,$pollerName="Cryptos") {
		$this->pollerName=$pollerName;		
		if ($pollerName=="") Nano\nanoCheck()->checkFailed("pollerName cannot be empty");
		parent::__construct($marketId,true);		
	}


	protected function setupMarket() {				
		$this->tradeFixedFees=array(1.5);
		$this->setupAssets();
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
	
	private function setupAssets() {
		$assetsCsv = Nano\nanoCsv()->csvContentToArray($this->assetsAsCsv(),';');
				
		$index=0;
		$assets=array();
		foreach($assetsCsv as $assetCsv) {
			//if ($index>=$assetCount) break;
			$asset=new Asset\Asset($assetCsv["assetId"]);
			$this->assets[]=$asset;
			$this->assetIds[]=$asset->assetId();
			$this->assetIdsByType[AssetType\CryptoCurrency]=$asset->assetId();
			++$index;
		} 		
		return $assets;
	}
	
	function marketReset() {
		$this->quotesCache=null;
		$this->cacheIndex=0;
		$this->cacheBeat=-1;
	}
	

	function addCustomSettings($ds) {		
		$ds->addRow(["pollerName","Nombre de fuente de cotizaciones",$this->pollerName]);
		$ds->addRow(["finalBeat","Último pulso",$this->finalBeat() ]);
	}	
	
	function quotesAsCsv() {		
		return Horus\callServiceMarketPollerCsv("q=marketQuotesAsCsv",$this->pollerName());
	}

	function lastQuotesAsCsv() {		
		$rows=$this->marketLastQuotes();
		
		$header=explode(",","marketBeat,assetId,buyQuote,sellQuote,reportedDate,pollTime,pollDate");
		$ds=new DataSet\DataSet($header);
		foreach($rows as $r) {
			$quote=$this->assetLastQuote($r["assetId"]);
			$ds->addRow([
					$r["marketBeat"],
					$this->textFormatter()->formatLink($r["assetId"],$this->assetPollSourceUrl($r["assetId"])),
					$this->textFormatter()->formatDecimal($r["buyQuote"],$quote->toAssetId(),""),
					$this->textFormatter()->formatDecimal($r["sellQuote"],$quote->toAssetId(),""),
					$r["reportedDate"],
					$r["pollTime"],
					$r["pollDate"]
				]);				
		}
		return $ds->toCsvRet();
	}

	private function assetsAsCsv() {		
		return Horus\callServiceMarketPollerCsv("q=assetsAsCsv",$this->pollerName());
	}

	function marketQuotes() {
		return Horus\callServiceMarketPollerArray("q=marketQuotesAsCsv",$this->pollerName());
	}

	function setupQuotesCache() {		
		$ret=Horus\callServiceMarketPollerArray("q=marketQuotesAsCsv",$this->pollerName());
		if (!is_array($ret[0])) throw new \Exception("quotesCache: csv content is not an array");		
		$this->quotesCache=$ret;
		return $ret;
	}


	function quotesCache() {				
		if ($this->quotesCache==null) $this->setupQuotesCache();
		return $this->quotesCache;
	}

	function marketLastQuotes() {		
		$cache=$this->quotesCache();
		$rows=[];

		$index=0;

		for($index=$this->cacheIndex;$cache[$index]["marketBeat"]==$this->cacheBeat;$index++) {
			$rows[]=$cache[$index];
			
			//printf("cacheIndex:%s cacheBeat:%s marketBeat:%s index:%s\n",$this->cacheIndex,$this->cacheBeat,$cache[$this->cacheIndex]["marketBeat"],$index);			
			//print_r($cache[$index]);
			//if ($index>30) exit("exit-index cacheIndex:".$cacheIndex);
			++$index;

		}

		return $rows;
	}

	function assetLastQuote($assetId):AssetQuotation\AssetQuotation {		
		$cache=$this->quotesCache();
		
		if  ($assetId==$this->defaultExchangeAssetId())
			 return new AssetQuotation\AssetQuotation($assetId,$this->defaultExchangeAssetId(), 1, 1);

		for($index=$this->cacheIndex;$cache[$index]["marketBeat"]==$this->cacheBeat;$index++) {
			$quote=$cache[$index];
			if ($quote["assetId"]==$assetId) {
				return new AssetQuotation\AssetQuotation($assetId,
					$this->defaultExchangeAssetId(),$quote["sellQuote"],$quote["buyQuote"]);	
			}
		}
		return new AssetQuotation\AssetQuotation($assetId,$this->defaultExchangeAssetId(), 0, 0);
	}

	function assetQuote($assetId):AssetQuotation\AssetQuotation {
		Nano\nanoPerformance()->track("yfTestMarket.assetQuote");
		$ret=$this->assetLastQuote($assetId);			
		Nano\nanoPerformance()->track("yfTestMarket.assetQuote");
		return $ret;
	}

	function beat($beat=null) {		
		if ($beat!=null) throw \Exception("unsupported for yfTestMarket");

		if ($this->cacheBeat==-1) $this->setupNextCacheIndex();
		return $this->cacheBeat;
	}

	function setupNextCacheIndex() {		
		$cache=$this->quotesCache(); // cache de activos (una fila con las cotizaciones de cada uno de los activos, las columnas son activo, cotizacion, pulso,marketBeat, etc. )


		if ($this->cacheBeat==-1) $this->cacheBeat=$this->quotesCache[0]["marketBeat"];

//		printf("in cacheIndex:%s  cacheBeat:%s ready:%s<br>\n",$this->cacheIndex,$this->cacheBeat,$this->ready());


		while($this->cacheIndex<count($cache) && $cache[$this->cacheIndex]["marketBeat"]<=$this->cacheBeat)  {
			++$this->cacheIndex;
//			printf("cacheIndex:%s  cacheBeat:%s<br>\n",$this->cacheIndex,$this->cacheBeat);
//			print_r($cache[$this->cacheIndex]);
//			if ($this->cacheIndex>200) exit("aca1");
		}


		if ($cache[$this->cacheIndex] == null) {
			$this->cacheBeat=$this->quotesCache[0]["marketBeat"];
		} else {
			$this->cacheBeat=$cache[$this->cacheIndex]["marketBeat"];
		}		
//		printf("out cacheIndex:%s  cacheBeat:%s ready:%s<br>\n",$this->cacheIndex,$this->cacheBeat,$this->ready());

	}

	function nextBeat() {		
		$this->setupNextCacheIndex();
		parent::nextBeat();
	}

	function assetPollSourceUrl($assetId) {
		return sprintf("https://finance.yahoo.com/quote/%s?p=%s",$assetId,$assetId);
	}

	function finalBeat() {
		$cache=$this->quotesCache();
		$lastQuote=$cache[count($cache)-1];
		return $lastQuote["marketBeat"];
	}

	function ready() {
		$cache=$this->quotesCache();

		$lastQuote=$cache[count($cache)-1];		

		$ret=$this->beat()<$lastQuote["marketBeat"];
		return $ret;
	}

  	function dehydrate() {
  		parent::dehydrate();  		
  		$this->quotesCache=null;
  		$this->cacheIndex=0;
//  		print "<br>QUOTES CACHE CLEANED<br>";
  	}
}

?>