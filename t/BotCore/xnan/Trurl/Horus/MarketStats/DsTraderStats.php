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
	const SEligibleByCycle=1000;	
	const SEligibleByQuantity=1001;	
	const SEligibleByEarn=1002;	
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

	function statsEligibleByQuantity($assetId) {
		return $this->statsMaxBuyQuantity($assetId)>0;
	}

	function statsEarn($assetId) {

	
		$buyQuote=$this->trader()->buyAtLimit($this->trader()->market(),$assetId,$this->trader()->buyLimitFactor() ); //1.01

		$sellQuote=$this->assetSellQuote($assetId);//*0.99;

		$earn=($sellQuote-$buyQuote)*$this->statsMaxBuyQuantity($assetId);

		return $earn;

	}

	function statsEligibleMarketByShortTrend() {
		return 
		$this->trader()->marketStats()->statsScalar(MarketStats\MarketStats::SLinearSlope,MarketStats\MarketStats::MarketIndexAsset) >=
			$this->trader()->marketShortTrendLowCut();
	}

	function statsEligibleMarketByMediumTrend() {
		return 
		$this->trader()->marketStatsMedium()->statsScalar(MarketStats\MarketStats::SLinearSlope,MarketStats\MarketStats::MarketIndexAsset) >=
			$this->trader()->marketMediumTrendLowCut();
	}

	function statsEligibleByShortTrend($assetId) {
		return 
		$this->trader()->marketStats()->statsScalar(MarketStats\MarketStats::SLinearSlope,$assetId) >=
			$this->trader()->assetShortTrendLowCut();
	}

	function statsEligibleByMediumTrend($assetId) {
		return 
		$this->trader()->marketStatsMedium()->statsScalar(MarketStats\MarketStats::SLinearSlope,$assetId) >=
			$this->trader()->assetMediumTrendLowCut();
	}

	function statsEligibleByEarn($assetId) { 
		return $this->statsEarn($assetId)>=$this->trader()->minEarn();		
	}

	function statsEligiblesFinal() { 
		$egs=[];
		foreach($this->marketStats()->market()->nonCurrencyAssetIds() as $assetId) {		
			if ($this->statsEligibleFinal($assetId)) {
				$egs[]=$assetId;
			}

		}

		return $this->statsSortAssetsByPreference($egs);
	}

	function statsSortAssetsByPreference(&$assetIds) {		
		$fn=function($assetId1,$assetId2) {			
			return $this->statsEarn($assetId1)<$this->statsEarn($assetId2);
		};

		usort($assetIds,$fn);		
		
		return $assetIds;
	}

	function statsEligibleByCycle($assetId) {
		return $this->assetCycle($assetId)<$this->trader()->buyCicleCut();
	}

	function statsEligibleFinal($assetId) {
		$egMarketTrend1=$this->statsEligibleMarketByShortTrend();
		$egMarketTrend2=$this->statsEligibleMarketByMediumTrend();
		$egTrend1=$this->statsEligibleByShortTrend($assetId);
		$egTrend2=$this->statsEligibleByMediumTrend($assetId);
		$egCycle=$this->statsEligibleByCycle($assetId);
		$egEarn=$this->statsEligibleByEarn($assetId);
		$egQuantity=$this->statsEligibleByQuantity($assetId);
		
		return $egMarketTrend1 && $egMarketTrend2 && $egTrend1 
			&& $egTrend2 && $egCycle && $egEarn && $egQuantity;
	}

	function statsScalar($statsDim,$assetId) { 

		if ($statsDim==self::SEligibleByCycle) {
			return $this->statsEligibleByCycle($assetId);
		}

		if ($statsDim==self::SEligibleByQuantity) {
			return $this->statsEligibleByQuantity($assetId);
		}

		if ($statsDim==self::SEligibleByEarn) {
			return $this->statsEligibleByEarn($assetId);
		}

		if ($statsDim==self::SEarn) {
			return $this->statsEarn($assetId);
		}

		Nano\nanoCheck()->checkFailed("traderStats: statsScalar: statsDim: $statsDim msg: unknown stats");
	}

}


?>