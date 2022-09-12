<?php
namespace xnan\Trurl\Horus\MarketPoll;
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

abstract class MarketPoll {
	var $pollingSkip=0;

	abstract function pollQuotes();
	abstract function pollHistory();

	function pollingSkip($pollingSkip=null) {
		if ($pollingSkip!=null) $this->pollingSkip=$pollingSkip;
			return $this->pollingSkip;
	}
}



?>