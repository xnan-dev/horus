<?php
namespace xnan\Trurl\Horus\MarketPoll;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Nano\DataSet;

Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

class Functions { const Load=1; }

class MarketPollManager {
	var $marketPolls=array();

	function __construct() {
		$this->addMarketPoll(new SimMarketPoll(),500);
	}

	 function addMarketPoll($marketPoll,$refreshMillis) {
		$marketPolls[]=array($marketPoll,$refreshMillis);
	}

	void poll() {
		foreach($this->marketPolls as $marketPoll) {
			$marketPoll->poll();
		} 
	}
}


?>