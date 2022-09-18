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

Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

class MathMarket extends AbstractMarket {
	var $quoteOffsetX=array();
	var $quoteOffsetY=array();
	var $assets=[];	
	var $assetIds=[];
	var $assetIdsByType=[];
	var $tradeFixedFees=[];
	var $assetCount;

	function __construct($marketId,$assetCount=1) {
		$this->assetCount=$assetCount;
		parent::__construct($marketId,false);
		$this->setupQuoteOffsets();
	}

	protected function setupMarket() {
		$this->setupAssets();
		$this->tradeFixedFees=array(1.5);		
		parent::setupMarket();		
	}

	function setupAssets() {		
		$this->assetIdsByType[AssetType\CryptoCurrency]=[];
		$this->assetIdsByType[AssetType\Currency]=[];		

		for($i=1;$i<=$this->assetCount;$i++) {
			$assetId="CC".$i;
			$asset=new Asset\Asset($assetId,AssetType\CryptoCurrency);
			$this->assetIds[]=$assetId;
			$this->assets[]=$asset;	
			$this->assetIdsByType[AssetType\CryptoCurrency]=$assetId;
		}

		$assetUsd=Asset\assetUsd();
		$this->assets[]=$assetUsd;
		$this->assetIds[]=$assetUsd->assetId();
		$this->assetIdsByType[AssetType\Currency]=$assetUsd->assetId();		
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

	function setupQuoteOffsets() {
		foreach($this->assetIds() as $assetId) {
			$this->quoteOffsetX[$assetId]=rand(0,100)/4;
			$this->quoteOffsetY[$assetId]=rand(0,100);
		}
	} 

 	function maxBuyQuantity(&$portfolio,$assetId) {
 		$portfolioCredit=$portfolio->currencyCredit($this);
 		$quote=$this->assetQuote($assetId);
 		$fixedFees=$this->tradeFixedFeesSum();
		$quantity=$portfolioCredit/$quote->buyQuote()-$fixedFees;
		return max($quantity,0);
 	}

	function assetQuote($assetId):AssetQuotation\AssetQuotation {
		Asset\checkAssetId($assetId);
		if ($assetId==Asset\assetUsd()->assetId()) {
			$aq=new AssetQuotation\AssetQuotation(Asset\assetUsd()->assetId(),Asset\assetUsd()->assetId(),1,1);
		} else {
			$base=10;
			$factor=0.3;
			$offsetX=$this->quoteOffsetX[$assetId];
			$offsetY=$this->quoteOffsetY[$assetId];
			$quote=$factor*$base*sin($this->fnTime()*5+$offsetX)+$base+$offsetY;
			$aq=new AssetQuotation\AssetQuotation(Asset\assetUsd()->assetId(),$assetId,$quote,$quote);			
		}		
		return $aq;
	}
	
	function fnTime() {
		return $this->beat()/100;
	}

	function ready() {		
		return true;
	}
	
	function quotesAsCsv() {
		return $this->lastQuotesAsCsv(); // TODO FIX
	}
	
	function lastQuotesAsCsv() {		
		$header=explode(",","assetId;buyQuote,pollTime");
		$dsQuotes=new DataSet\DataSet($header);	

		foreach($this->assets as $asset) {
			$quote=$this->assetQuote($asset->assetId());
			$quote->buyQuote();
			$line=array($asset->assetId(),$quote->buyQuote(),time());
			$dsQuotes->addRow($line);					
		}
		return $dsQuotes->toCsvRet();
	}

	function marketLastQuotes() {
		$ret=Nano\nanoCsv()->csvContentToArray($this->lastQuotesAsCsv(),';');		
		return $ret;
	}
}

?>