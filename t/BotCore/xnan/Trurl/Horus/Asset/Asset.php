<?php
namespace xnan\Trurl\Horus\Asset;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\AssetType;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

AssetType\Functions::Load;

class Functions { const Load=1; }

class Asset {
	var $assetId;
	var $assetType;
	
	function __construct($assetId="A0",$assetType=AssetType\CryptoCurrency) {
		$this->assetId=$assetId;
		$this->assetType=$assetType;
	}
	
	function assetType() {
		return $this->assetType;
	}

	function assetId() {
		return $this->assetId;
	}
	 
}

function assetUsd() {
	$assetUsd=new Asset("USD",AssetType\Currency);	
	return $assetUsd;
}

function checkAssetId($assetId) {
	if (!is_string($assetId) || $assetId=="") Nano\nanoCheck()->checkFailed("checkAssetId: assetId msg: should be a string");
}

?>