<?php
namespace xnan\Trurl\Horus\Portfolio;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Horus\WorldSettings;
use xnan\Trurl\Horus\BotWorld;
AssetType\Functions::Load;
Asset\Functions::Load;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

class Functions { const Load=1; }

class Portfolio {
	private $portfolioId=null;

	function __construct($portfolioId=null) {
		if ($portfolioId==null) Nano\nanoCheck()->checkFailed("portfolioId should not be null");
		
		$this->portfolioId=$portfolioId;

		$this->setupSettings();
	}	

	function setupSettings() {
		BotWorld\BotWorld::instance()->settings()
			->registerChangeListener("portfolio.asset.quantity",$this);	
		BotWorld\BotWorld::instance()->settings()
			->registerChangeListener("portfolio.recover",$this);
	}

	function onSettingsChange($key,$params) {			
		if ($params["portfolioId"]==$this->portfolioId()) {
			if ($key=="portfolio.asset.quantity") {
				$assetId=$params["assetId"];
				$this->assetQuantity[$assetId]=$params["settingsValue"];
				$this->lastDepositTime(time());
				$this->lastDepositQuantity($this->assetQuantity[$assetId]); //TODO: considerar solo asset moneda default.
			}
			if ($key=="portfolio.recover" && $params["settingsValue"]=="true") {
				$assetId=$params["assetId"];				
				$this->portfolioRecover();
			}

		}
	}

	function portfolioReset() {
		$this->assetQuantity=array();
		$this->lastDepositQuantity=0;
		$this->lastDepositTime=time();
	}

	function portfolioId() {		
		return $this->portfolioId;
	}

	function lastDepositTime($time=null) {
		return Horus\persistence()->portfolioLastDepositTime($this->portfolioId(),$time);
	}

	function lastDepositQuantity($quantity=null) {
		return Horus\persistence()->portfolioLastDepositQuantity($this->portfolioId(),$quantity);
	}
	
	function assetIds() {		
		return Horus\persistence()->portfolioAssetIds($this->portfolioId());
	}

	function assetQuantity($assetId) {
		return Horus\persistence()->portfolioAssetQuantity($this->portfolioId(),$assetId);
	}

	function assetQuantities() {
		return Horus\persistence()->portfolioAssetQuantities($this->portfolioId());
	}

	function addAssetQuantity($assetId,$quantity,&$market,$isDeposit=false) {
		Horus\persistence()->portfolioAddAssetQuantity($this->portfolioId(),$assetId,$quantity,$market,$isDeposit);
	}

	function nonCurrencyAssetIds(&$market) {
		$as=array();
		foreach($this->assetQuantities() as $assetId=>$quantity) {
			$asset=$market->assetById($assetId);
			if($asset->assetType()!=AssetType\Currency && $quantity>0) {
				$as[]=$assetId;
			}
		}
		return $as;
	}

	function removeAssetQuantity($assetId,$quantity,&$market) {
		Horus\persistence()->portfolioRemoveAssetQuantity($this->portfolioId(),$assetId,$quantity,$market);
	}

	function currencyAssetIds(&$market) {
		$as=array();

		foreach($this->assetQuantities() as $assetId=>$quantity) {
			$asset=$market->assetById($assetId);
			if($asset->assetType()==AssetType\Currency) {
				$as[]=$assetId;
			}
		}
		return $as;
	}

	function monthRoi(&$market) {
		$delta=time()-$this->lastDepositTime();		
		$gain=$market->portfolioValuation($this)-$this->lastDepositQuantity();
		$roi=100*$gain/$this->lastDepositQuantity();
		$monthRoi=$delta > 0 ? $roi*30*24*60*60/$delta : 0;
		return $monthRoi;
	}

	function currencyCredit(&$market) {
		$as=$this->currencyAssetIds($market);
		if (count($as)==0) return 0;
		$currencyAssetId=$as[0];
		return $this->assetQuantity($currencyAssetId);
	}

	function __toString() {
		return toCanonical($this);
	}

	function portfolioAsCsv() {
		$header=explode(",","assetId,assetQuantity");
		$ds=new DataSet\DataSet($header);		
		foreach($this->assetQuantities() as $assetId=>$assetQuantity) {
			$ds->addRow([$assetId,$assetQuantity ]);				
		}
		return $ds->toCsvRet();
	}

}

function toCanonical($portfolio) {
	$s="";
	foreach ($portfolio->assetQuantities() as $assetId=>$quantity) {
		if (strlen($s)>0) $s.=" ";
		$s.=sprintf("%s:%s",$assetId,Nano\nanoTextFormatter()->quantityToCanonical($quantity));
	}
	return sprintf("Portfolio [%s]",$s);
}


?>