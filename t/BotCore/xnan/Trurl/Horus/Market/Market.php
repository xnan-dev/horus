<?php
namespace xnan\Trurl\Horus\Market;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano\Observer;

Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

class Functions { const Load=1; }

interface Market {

	function onBeat();

	function assetById($assetId);

	function assetIds();

	function assetIdsByType($assetType);

 	function marketId();

 	function maxBuyQuantity(&$portfolio,$assetId);

 	function maxSellQuantity(&$portfolio,$assetId);

	function assetTrade(&$portfolio,$assetId,$tradeOp=AssetTradeOperation\AssetBuy,$quantity=1);

	function assetQuote($assetId):AssetQuotation\AssetQuotation;
	
	function portfolioValuation($portfolio);

	function beat();

	function nextBeat();

	function ready();

	function settingsAsCsv();

	function marketTitle($markettitle);
}

?>