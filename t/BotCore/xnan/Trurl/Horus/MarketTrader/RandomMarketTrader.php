<?php
namespace xnan\Trurl\Horus\MarketTrader;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Horus\AssetTradeStatus;
use xnan\Trurl\Horus\CryptoCurrency;
use xnan\Trurl\Horus\AssetType;

AssetTradeOperation\Functions::Load;

class RandomMarketTrader extends MarketTrader {
	function __construct($traderId,&$portfolio) {
		MarketTrader::__construct($traderId,$portfolio);
	}


	function trade(&$market) {
		$op_rand=rand(1,3);
		if ($op_rand<3) {
			$op=$op_rand==1 ? AssetTradeOperation\Buy:AssetTradeOperation\Sell;

			$assetIndex=random_int(0, count($market->assetIdsByType(AssetType\CryptoCurrency))-1 );
			$buyAssetId=$market->assetIds()[$assetIndex];		
			$sellCandidates=$this->portfolio->nonCurrencyAssetIds($market);
			$sellAssetIndex=count($sellCandidates)>0 ? random_int(0, count($sellCandidates)-1 ) : 0;
			$sellAssetId=count($sellCandidates)>0 ? $sellCandidates[$sellAssetIndex] : "";


			if ($op==AssetTradeOperation\Sell && $sellAssetId=="")  $op=AssetTradeOperation\Buy;
			$quantity=100*rand(1,10);
			 if ($op==AssetTradeOperation\Buy)  {
			 	$quantity=min($quantity,$market->maxBuyQuantity($this->portfolio,$buyAssetId));
			 } 
			 if ($op==AssetTradeOperation\Sell) {
			 	$quantity=min($quantity,$market->maxSellQuantity($this->portfolio,$sellAssetId));
			 }

			$assetId=$op==AssetTradeOperation\Buy ? $buyAssetId : $sellAssetId;

			
			$buyQuote=$this->buyAtLimit($market,$assetId,1.05);
			$sellQuote=$this->sellAtLimit($market,$assetId,0.95);

			$quote=$op==AssetTradeOperation\Buy ? $buyQuote : $sellQuote;

			if ($quantity!=0 && !$this->hasOrderQueueByStatusAndAssetId(AssetTradeStatus\Suggested,$assetId) )  { 
 				$this->queueOrder($assetId,$op,$quantity,$quote);
			}
		} 
	}
}

?>