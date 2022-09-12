<?php
namespace xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

class Functions { const Load=1; }

class AssetQuotation {
	var $fromAssetId,$toAssetId;
	var $sellQuote,$buyQuote;

	function __construct($fromAssetId,$toAssetId,$sellQuote=0,$buyQuote=0) {
		Asset\checkAssetId($fromAssetId);
		Asset\checkAssetId($toAssetId);
		$this->fromAssetId=$fromAssetId;
		$this->toAssetId=$toAssetId;
		$this->sellQuote=$sellQuote;
		$this->buyQuote=$buyQuote;
	}

	function fromAssetId() {
		return $this->fromAssetId;
	}

	function toAssetId() {
		return $this->toAssetId;	
	}

	function sellQuote() {
		return $this->sellQuote;
	}

	function buyQuote() {
		return $this->buyQuote;
	}

	function __toString() {
		return sprintf("%s: %s|%s",$this->fromAssetId(),Nano\nanoTextFormatter()->moneyLegend($this->buyQuote()), Nano\nanoTextFormatter()->moneyLegend($this->sellQuote()) );
	}
}

?>