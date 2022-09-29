<?php
namespace xnan\Trurl\Horus\MarketStats;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Nano\TextFormatter;
use xnan\Trurl\Nano\Performance;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Hydra;
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HMatrixes;
use xnan\Trurl\Hydra\HMatrixes\HMatrix;
use xnan\Trurl\Hydra\HMatrixes\HPdoMatrix;
use xnan\Trurl\Horus\MarketStats;

Hydra\Functions::Load;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End


class DsTraderStats {
	const SElegibleByCycle=1000;	
	const SElegibleByQuantity=1001;	
	const SElegibleByEarn=1002;	
	const SEarn=1003;	

	private $marketStats;
	private $trader;

	function __construct(&$marketStats,&$trader) {		
		Nano\nanoCheck()->checkNotNull($marketStats,"marketStats should not be null");
		Nano\nanoCheck()->checkNotNull($trader,"trader should not be null");
		$this->marketStats=$marketStats;
		$this->trader=$trader;
	}

	function trader() {
		return $this->trader;
	}

	function marketStats() {
		return $this->marketStats;
	}

	function assetCycle($assetId) {
		return $this->marketStats()->statsScalar(MarketStats\MarketStats::SCicle,$assetId);
	}


	function assetSellQuote($assetId) {
		return $this->trader()->cicleToValue($this->trader()->sellCicleCut(),
					$this->marketStats()->statsScalar(MarketStats\MarketStats::SMin,$assetId),
					$this->marketStats()->statsScalar(MarketStats\MarketStats::SMax,$assetId)
				);

	}

	function statsMaxBuyQuantity($assetId) {
		$quantity=$this->trader()->maxBuyQuantityByStrategy($this->trader()->market(),$assetId);			
		$quantity=min($quantity,$this->trader()->market()->maxBuyQuantity($this->trader()->portfolio() ,$assetId));

		return $quantity;

	}

	function statsElegibleByQuantity($assetId) {
		return $this->statsMaxBuyQuantity($assetId)>0;
	}

	function statsEarn($assetId) {

	
		$buyQuote=$this->trader()->buyAtLimit($this->trader()->market(),$assetId,$this->trader()->buyLimitFactor() ); //1.01

		$sellQuote=$this->assetSellQuote($assetId);//*0.99;

		$earn=($sellQuote-$buyQuote)*$this->statsMaxBuyQuantity($assetId);

		return $earn;

	}

	function statsElegibleByEarn($assetId) { // SEGUIR ACA
		return $this->statsEarn($assetId)>=$this->trader()->minEarn();		
	}

	function statsElegibleByCycle($assetId) {
		return $this->assetCycle($assetId)<$this->trader()->buyCicleCut();
	}

	function statsElegibleFinal($assetId) {
		$egCycle=$this->statsElegibleByCycle($assetId);
		$egEarn=$this->statsElegibleByEarn($assetId);
		$egQuantity=$this->statsElegibleByQuantity($assetId);
		return $egCycle && $egEarn && $egQuantity;
	}

	function statsScalar($statsDim,$assetId) { 

		if ($statsDim==self::SElegibleByCycle) {
			return $this->statsElegibleByCycle($assetId);
		}

		if ($statsDim==self::SElegibleByQuantity) {
			return $this->statsElegibleByQuantity($assetId);
		}

		if ($statsDim==self::SElegibleByEarn) {
			return $this->statsElegibleByEarn($assetId);
		}

		if ($statsDim==self::SEarn) {
			return $this->statsEarn($assetId);
		}

		Nano\nanoCheck()->checkFailed("traderStats: statsScalar: statsDim: $statsDim msg: unknown stats");
	}

}


?>