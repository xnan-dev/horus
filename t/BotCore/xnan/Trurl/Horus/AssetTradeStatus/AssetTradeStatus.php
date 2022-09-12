<?php

namespace xnan\Trurl\Horus\AssetTradeStatus;
use xnan\Trurl;
use xnan\Trurl\Horus;

class Functions { const Load=1; }

const Suggested=1;
const Rejected=2;
const Approved=3;
const Cancelled=4;
const Done=5;

function toCanonical($status) {
	if ($status==Suggested) return "Suggested";
	if ($status==Rejected) return "Rejected";
	if ($status==Approved) return "Approved";
	if ($status==Cancelled) return "Cancelled";
	if ($status==Done) return "Done"; // NO SE USA ? (se filtra por estatus approved y done=1)
	return "Unknown($status)";
}
?>