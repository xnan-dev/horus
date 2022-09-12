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
	var $assetQuantity=array();
	var $portfolioId=null;
	var $lastDepositQuantity=0;
	var $lastDepositTime=null;

	function __construct($portfolioId=null, $lastDepositTime=null,$lastDepositQuantity=0) {

		if ($portfolioId==null) {
			$portfolioId=rand(1,1000*1000);	
		}
		if ($lastDepositTime==null) {
			$lastDepositTime=time();
		}
		
		$this->portfolioId=$portfolioId;
		$this->lastDepositQuantity=$lastDepositQuantity;
		$this->lastDepositTime=$lastDepositTime;

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

	function lastDepositTime($time=null) {
		if ($time!=null) $this->lastDepositTime=$time;
		return $this->lastDepositTime;
	}

	function lastDepositQuantity($quantity=null) {
		if ($quantity!=null) $this->lastDepositQuantity=$quantity;
		return $this->lastDepositQuantity;
	}

	function portfolioId($portfolioId=null) {
		if ($portfolioId!=null) $this->portfolioId=$portfolioId;
		return $this->portfolioId;
	}
	
	function assetIds() {
		$this->cleanAssetIds();
		return array_keys($this->assetQuantity);
	}

	private function cleanAssetIds() {
		foreach ($this->assetQuantity as $assetId=>$quantity) {
			//if ($quantity==0) unset($this->assetQuantity[$assetId]);
		}
	}

	function assetQuantity($assetId) {
		if (!array_key_exists($assetId,$this->assetQuantity)) return 0;
		return $this->assetQuantity[$assetId];
	}

	function addAssetQuantity($assetId,$quantity,&$market,$isDeposit=false) {
		Asset\checkAssetId($assetId);
		if (!array_key_exists($assetId,$this->assetQuantity)) $this->assetQuantity[$assetId]=0;
		$this->assetQuantity[$assetId]+=$quantity;
		if ($isDeposit) {
			$this->lastDepositTime(time());
			$this->lastDepositQuantity($quantity);	
		} 

	}

	function nonCurrencyAssetIds(&$market) {
		$as=array();
		foreach($this->assetQuantity as $assetId=>$quantity) {
			$asset=$market->assetById($assetId);
			if($asset->assetType()!=AssetType\Currency && $quantity>0) {
				$as[]=$assetId;
			}
		}
		return $as;
	}

	function removeAssetQuantity($assetId,$quantity,&$market) {
		if (!array_key_exists($assetId,$this->assetQuantity)) return;
		$this->assetQuantity[$assetId]-=$quantity;
	}

	function currencyAssetIds(&$market) {
		$as=array();
		foreach($this->assetQuantity as $assetId=>$quantity) {
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
		return $this->assetQuantity[$currencyAssetId];
	}

	function __toString() {
		return toCanonical($this);
	}

	function portfolioAsCsv() {
		$header=explode(",","assetId,assetQuantity");
		$ds=new DataSet\DataSet($header);
		foreach($this->assetQuantity as $assetId=>$assetQuantity) {
			$ds->addRow([$assetId,$assetQuantity ]);				
		}
		return $ds->toCsvRet();
	}

	function save() {
		Nano\nanoCheck()->checkDiskAvailable();

		if (!file_exists("content/Portfolio")) mkdir("content/Portfolio");
		file_put_contents(sprintf("content/Portfolio/portfolio.%s.csv",$this->portfolioId()),$this->portfolioAsCsv() );		
	}

	function portfolioRecover() {
		$fileName=sprintf("content/Portfolio/portfolio.%s.csv",$this->portfolioId());
		if (file_exists($fileName)) {
			$csv=file_get_contents($fileName );
			$rows=Nano\nanoCsv()->csvContentToArray($csv,';');
			foreach($rows  as $row) {
				$assetId=$row["assetId"];
				$assetQuantity=$row["assetQuantity"];
				$this->assetQuantity[$assetId]=$assetQuantity;
			}		

			Nano\msg(sprintf("Portfolio: portfolioId:%s portfolioRecover: done",$this->portfolioId() ));
		} else {
			Nano\msg(sprintf("Portfolio: portfolioId:%s portfolioRecover: ignored msg: no file from which recover",$this->portfolioId() ));
		}

	}
}

function toCanonical($portfolio) {
	$s="";
	foreach ($portfolio->assetQuantity as $assetId=>$quantity) {
		if (strlen($s)>0) $s.=" ";
		$s.=sprintf("%s:%s",$assetId,Nano\nanoTextFormatter()->quantityToCanonical($quantity));
	}
	return sprintf("Portfolio [%s]",$s);
}


?>