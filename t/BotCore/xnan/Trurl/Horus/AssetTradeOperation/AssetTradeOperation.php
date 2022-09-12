<?php

namespace xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl;
use xnan\Trurl\Horus;

class Functions { const Load=1; }

const Sell=1;
const Buy=2;

function toCanonical($op) {
	if ($op==Sell) return "Sell";
	if ($op==Buy) return "Buy";
	return "Unknown";
}
?>