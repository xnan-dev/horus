<?php

namespace xnan\Trurl\Horus\AssetTradeOrder;
use xnan\Trurl;
use xnan\Trurl\Horus;

class Functions { const Load=1; }

class AssetTradeOrder {
	var	$traderId;
	var	$queueId;
	var	$assetId;
	var	$tradeOp;
	var	$quantity;
	var	$targetQuote;
	var	$status;
	var	$done;
	var	$statusChangeBeat=0;
	var $statusChangeTime=null;
	var $queueBeat=0;
	var $parentQueueId=null;
	var $notified=false;
	var $doneBeat=null;
	var $doneTime=null;
	var $doneQuote=null;

	function __construct() {
	}

	function traderId($traderId=null) {
		if ($traderId!=null) $this->traderId=$traderId;
		return $this->traderId;
	}

	function queueId($queueId=null) {
		if ($queueId!=null) $this->queueId=$queueId;
		return $this->queueId;
	}

	function parentQueueId($queueId=null) {
		if ($queueId!=null) $this->parentQueueId=$queueId;
		return $this->parentQueueId;
	}

	function assetId($assetId=null) {
		if ($assetId!=null) $this->assetId=$assetId;
		return $this->assetId;
	}

	function statusChangeBeat($statusChangeBeat=null) {
		if ($statusChangeBeat!=null) $this->statusChangeBeat=$statusChangeBeat;
		return $this->statusChangeBeat;
	}

	function statusChangeTime($statusChangeTime=null) {
		if ($statusChangeTime!=null) $this->statusChangeTime=$statusChangeTime;
		return $this->statusChangeTime;
	}

	function doneBeat($doneBeat=null) {
		if ($doneBeat!=null) $this->doneBeat=$doneBeat;
		return $this->doneBeat;
	}

	function doneTime($doneTime=null) {
		if ($doneTime!=null) $this->doneTime=$doneTime;
		return $this->doneTime;
	}

	function tradeOp($tradeOp=null) {
		if ($tradeOp!=null) $this->tradeOp=$tradeOp;
		return $this->tradeOp;
	}

	function quantity($quantity=null) {
		if ($quantity!=null) $this->quantity=$quantity;
		return $this->quantity;
	}

	function targetQuote($targetQuote=null) {
		if ($targetQuote!=null) $this->targetQuote=$targetQuote;
		return $this->targetQuote;
	}

	function doneQuote($doneQuote=null) {
		if ($doneQuote!=null) $this->doneQuote=$doneQuote;
		return $this->doneQuote;
	}

	function status($status=null) {
		if ($status!=null) $this->status=$status;
		return $this->status;
	}

	function done($done=null) {
		if ($done!=null) $this->done=$done;
		return $this->done;
	}

	function notified($notified=null) {
		if ($notified!=null) $this->notified=$notified;
		return $this->notified;
	}

	function queueBeat($queueBeat=null) {
		if ($queueBeat!=null) $this->queueBeat=$queueBeat;
		return $this->queueBeat;
	}

}

?>