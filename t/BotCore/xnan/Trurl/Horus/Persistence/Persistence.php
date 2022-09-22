<?php
namespace xnan\Trurl\Horus\Persistence;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Horus\AssetTradeStatus;
use xnan\Trurl\Horus\AssetTradeOrder;
use xnan\Trurl\Horus\CryptoCurrency;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Nano\TextFormatter;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Horus\BotArena;
use xnan\Trurl\Horus\WorldSettings;
use xnan\Trurl\Horus\Portfolio;
use xnan\Trurl\Horus\Asset;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

Asset\Functions::Load;


class Persistence {
	static $instance;


	static function instance() {
		if (self::$instance==null) {
			self::$instance=new Persistence();
		}
		return self::$instance;
	}


	function pdo(&$pdo=null) {
		if ($pdo!=null) {
			$this->pdo=$pdo;
		} else {
			if ($this->pdo==null) {
				throw new \Exception("pdo not yet setup");
			}
		}

		return $this->pdo;
	}

	function marketPollContentMaxAgeSeconds($marketId) {
		return $this->marketField($marketId,"pollContentMaxAgeSeconds");
	}

	function marketBeat($marketId,$beat=null) {		
		if ($beat!=null) {			
			$this->marketFieldSetInt($marketId,"beat",$beat);
		}
		return $this->marketField($marketId,"beat");
	}

	function marketTitle($marketId,$marketTitle=null) {		
		if ($marketTitle!=null) {			
			$this->marketFieldSetString($marketId,"marketTitle",$marketTitle);
		}
		return $this->marketFieldString($marketId,"marketTitle");
	}

	function marketQuoteDecimals($marketId,$quoteDecimals=null) {		
		if ($quoteDecimals!=null) {			
			$this->marketFieldSetInt($marketId,"quoteDecimals",$quoteDecimals);
		}
		return $this->marketField($marketId,"quoteDecimals");
	}

	function marketDefaultExchangeAssetId($marketId,$defaultExchangeAssetId=null) {		
		if ($defaultExchangeAssetId!=null) {			
			$this->marketFieldSetString($marketId,"defaultExchangeAssetId",$defaultExchangeAssetId);
		}
		$v=$this->marketFieldString($marketId,"defaultExchangeAssetId");
		Nano\nanoCheck()->checkNotNull($v,"marketId:$marketId defaultExchangeAssetId should not be null");
		return $v;
	}

	function yfMarketLastBeatRead($marketId,$lastBeatRead=null) {		
		return $this->yfMarketFieldInt($marketId,"lastBeatRead",$lastBeatRead);		
	}

	function traderOrderQueue($botArenaId,$traderId) {		
		$os=[];		
		$query=sprintf("SELECT * FROM assetTradeOrder as o						
							WHERE botArenaId='%s' 
								AND traderId='%s'",$botArenaId,$traderId);

		$r=$this->pdoQuery($query);

		while  ($row=$r->fetch()) {

			$o=new AssetTradeOrder\AssetTradeOrder();

			$o->botArenaId($row["botArenaId"]);
			$o->traderId($row["traderId"]);
			$o->queueId($row["queueId"]);
			$o->parentQueueId($row["parentQueueId"]);
			$o->assetId($row["assetId"]);
			$o->statusChangeBeat($row["statusChangeBeat"]);
			$o->statusChangeTime($row["statusChangeTime"]);
			$o->doneBeat($row["doneBeat"]);
			$o->doneTime($row["doneTime"]);
			$o->tradeOp($row["tradeOp"]);
			$o->quantity($row["quantity"]);
			$o->targetQuote($row["targetQuote"]);
			$o->doneQuote($row["doneQuote"]);
			$o->status($row["status"]);
			$o->done( $row["done"]==1 );
			$o->notified($row["notified"]);
			$o->queueBeat($row["queueBeat"]);
			$os[]=$o;
		}		
		return $os;
	}

	private function marketField($marketId,$field) {
		return $this->marketRow($marketId)[$field];
	}

	private function marketFieldString($marketId,$field) {
		return $this->marketRow($marketId)[$field];
	}

	private function marketFieldSetInt($marketId,$field,$value) {
		$query=sprintf(
			"UPDATE market SET $field=$value WHERE marketId='%s'",
				$marketId);		

		$this->pdoQuery($query);
	}

	private function marketFieldSetString($marketId,$field,$value) {
		$query=sprintf(
			"UPDATE market SET $field='$value' WHERE marketId='%s'",
				$marketId);		

		$this->pdoQuery($query);
	}

	private function yfMarketFieldInt($marketId,$field,$value=null) {
		if ($value!=null) {

			$query=sprintf(
				"UPDATE yahooFinanceMarket 
					SET $field=$value 
					WHERE
					 marketId='%s'
				",
					$marketId);		

			$this->pdoQuery($query);

			return null;
		} else {
			return $this->yfMarketRow($marketId)[$field];
		}
	}

	private function dsTraderFieldInt($botArenaId,$traderId,$field,$value=null) {
		if ($value!=null) {

			$query=sprintf(
				"UPDATE divideAndScaleMarketTrader 
					SET $field=$value 
					WHERE
					 botArenaId='%s' AND
					 traderId='%s'
				",
					$botArenaId,
					$traderId);		

			$this->pdoQuery($query);

			return null;
		} else {
			return $this->dsTraderRow($botArenaId,$traderId)[$field];
		}
	}


	private function traderFieldString($botArenaId,$traderId,$field,$value=null) {
		return $this->traderFieldInt($botArenaId,$traderId,$field,$value);
	}

	private function traderFieldInt($botArenaId,$traderId,$field,$value=null) {
		if ($value!=null) {

			$query=sprintf(
				"UPDATE marketTrader 
					SET $field=$value 
					WHERE
					 botArenaId='%s' AND
					 traderId='%s'
				",
					$botArenaId,
					$traderId);		

			$this->pdoQuery($query);

			return null;
		} else {
			return $this->traderRow($botArenaId,$traderId)[$field];
		}
	}

	private function traderFieldDouble($botArenaId,$traderId,$field,$value=null) {
		if ($value!=null) {
			$value=round($value, 10);

			$query=sprintf(
				"UPDATE marketTrader 
					SET $field=$value 
					WHERE
					 botArenaId='%s' AND
					 traderId='%s'
				",
					$botArenaId,
					$traderId);		

			$this->pdoQuery($query);

			return null;
		} else {
			return $this->traderRow($botArenaId,$traderId)[$field];
		}
	}

	private function marketBotArenaId($marketId) {

		$query=sprintf(
			"SELECT botArenaId FROM botArena
				WHERE
				 marketId='%s'
			",
				$marketId
			);		

		$r=$this->pdoQuery($query);

		if ($row=$r->fetch()) {
			return $row["botArenaId"];
		}

		Nano\nanoCheck()->checkFailed("botArena not found");
	}

	private function dsTraderFieldDouble($botArenaId,$traderId,$field,$value=null) {
		if ($value!=null) {

			$value=round($value, 10);

			$query=sprintf(
				"UPDATE divideAndScaleMarketTrader 
					SET $field=$value 
					WHERE
					 botArenaId='%s' AND
					 traderId='%s'
				",
					$botArenaId,
					$traderId);		

			$this->pdoQuery($query);

			return null;
		} else {
			return $this->dsTraderRow($botArenaId,$traderId)[$field];
		}
	}

	private function marketRow($marketId) {
		$query=sprintf(
		"SELECT * FROM market WHERE marketId='%s'",
			$marketId);		

		$r=$this->pdoQuery($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("marketStats row not found");
		}		
	}

	private function yfMarketRow($marketId) {
		$query=sprintf(
		"SELECT yf.*,m.* FROM yahooFinanceMarket yf
			INNER JOIN market m ON 
				yf.marketId=m.marketId
			WHERE
				yf.marketId='%s'",
			$marketId);		

		$r=$this->pdoQuery($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("yfMarket row not found");
		}		
	}




	private function dsTraderRow($botArenaId,$traderId) {
		$query=sprintf(
		"SELECT * 
			FROM marketTrader t
			INNER JOIN divideAndScaleMarketTrader ds 
				ON t.botArenaId=ds.botArenaId AND t.traderId=ds.traderId
			WHERE 
				t.traderId='%s' AND
				t.botArenaId='%s'
		"
		,$traderId
		,$botArenaId);		

//		print $query;

		$r=$this->pdoQuery($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("dsTrader row not found");
		}		
	}

	private function traderRow($botArenaId,$traderId) {
		$query=sprintf(
		"SELECT * 
			FROM marketTrader t
			WHERE 
				t.traderId='%s' AND
				t.botArenaId='%s'
		"
		,$traderId
		,$botArenaId);		

		$r=$this->pdoQuery($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("trader row not found for botArenaId:'$botArena' traderId:'$traderId'");
		}		
	}


	function dsTraderWaitBeats($botArenaId,$marketId,$waitBeats=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"waitBeats",$waitBeats);
	}

	function dsTraderPhase($botArenaId,$marketId,$phase=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"phase",$phase);
	}

	function dsTraderStartBeat($botArenaId,$marketId,$startBeat=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"startBeat",$startBeat);	
	}

	function dsTraderMaxAssetPercentage($botArenaId,$marketId,$maxAssetPercentage=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"maxAssetPercentage",$maxAssetPercentage);	
	}

	function dsTraderWaitBeatsForAssetRepeat($botArenaId,$marketId,$waitBeatsForAssetRepeat=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"waitBeatsForAssetRepeat",$waitBeatsForAssetRepeat);	
	}

	function dsTraderBuyCicleCut($botArenaId,$marketId,$buyCicleCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"buyCicleCut",$buyCicleCut);	
	}

	function dsTraderSellCicleCut($botArenaId,$marketId,$sellCicleCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"sellCicleCut",$sellCicleCut);	
	}

	function dsTraderMaxBuySuggestions($botArenaId,$marketId,$maxBuySuggestions=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"maxBuySuggestions",$maxBuySuggestions);	
	}

	function dsTraderBuyLimitFactor($botArenaId,$marketId,$buyLimitFactor=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"buyLimitFactor",$buyLimitFactor);	
	}

	function worldBotArenas() {		
		$r=$this->pdoQuery("SELECT * FROM botArena");
		$as=[];
		while ($row=$r->fetch()) {
			$botArenaId=$row["botArenaId"];
			$b=new BotArena\BotArena($botArenaId,$row["marketId"]);
			$as[$botArenaId]=$b;
		}
		return $as;		
	}


	function traderNextQueueId($botArenaId,$traderId) {
		$query=
		$r=$this->pdoQuery(sprintf("
				SELECT COALESCE(MAX(queueId)+1,0) as nextQueueId 
					FROM assetTradeOrder
					WHERE botArenaId='%s' AND traderId='%s'"
					,$botArenaId,$traderId));

		$row=$r->fetch();
		return $row["nextQueueId"];	
	}

	function traderAutoApprove($botArenaId,$traderId,$autoApprove=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"autoApprove",$autoApprove);	
	}

	function traderMinFlushBeats($botArenaId,$traderId,$minFlushBeats=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"minFlushBeats",$minFlushBeats);	
	}

	function traderSettingBuyUnits($botArenaId,$traderId,$settingBuyUnits=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"settingBuyUnits",$settingBuyUnits);	
	}

	function traderSettingBuyMinimum($botArenaId,$traderId,$settingBuyMinimum=null) {
		return $this->traderFieldDouble($botArenaId,$traderId,"settingBuyMinimum",$settingBuyMinimum);	
	}

	function traderMinEarn($botArenaId,$traderId,$minEarn=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"minEarn",$minEarn);	
	}

	function traderNotificationsEnabled($botArenaId,$traderId,$notificationsEnabled=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"notificationsEnabled",$notificationsEnabled);	
	}

	function traderAutoCancelBuyBeats($botArenaId,$traderId,$autoCancelBuyBeats=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"autoCancelBuyBeats",$autoCancelBuyBeats);	
	}

	function traderTitle($botArenaId,$traderId,$traderTitle=null) {
		return $this->traderFieldString($botArenaId,$traderId,"traderTitle",$traderTitle);	
	}
	
	function traderDailyWaitFromMarketOpen($botArenaId,$traderId,$dailyWaitFromMarketOpen=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"dailyWaitFromMarketOpen",$dailyWaitFromMarketOpen);	
	}

	function traderDailyWaitFromMarketClose($botArenaId,$traderId,$dailyWaitFromMarketClose=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"dailyWaitFromMarketClose",$dailyWaitFromMarketClose);	
	}

	function traderOpenTabToBroker($botArenaId,$traderId,$openTabToBroker=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"openTabToBroker",$openTabToBroker);	
	}

	function traderTelegramChatId($botArenaId,$traderId,$telegramChatId=null) {
		return $this->traderFieldInt($botArenaId,$traderId,"telegramChatId",$telegramChatId);	
	}

	function portfolioLastDepositTime($portfolioId=null,$lastDepositTime=null) {
		return $this->portfolioFieldInt($portfolioId,"lastDepositTime",$lastDepositTime);	
	}

	function portfolioLastDepositQuantity($portfolioId=null,$lastDepositQuantity=null) {
		return $this->portfolioFieldInt($portfolioId,"lastDepositQuantity",$lastDepositQuantity);	
	}
	
	function portfolioAssetIds($portfolioId=null) {		

		$query=sprintf(
			"SELECT DISTINCT(assetId) FROM portfolioAsset
				WHERE
				 portfolioId='%s'
			",
				$portfolioId
			);		

		$r=$this->pdoQuery($query);
		
		$assets=[];

		while ($row=$r->fetch()) {
			$assets[]=$row["assetId"];
		}

		return $assets;
	}

	function portfolioAssetQuantity($portfolioId,$assetId) {
		$query=sprintf(
			"SELECT portfolioId,assetId,SUM(assetQuantity) as assetQuantity FROM portfolioAsset
				WHERE
				 portfolioId='%s' AND
				 assetId='%s'
				 GROUP By
				 	portfolioId,assetId
			",
				$portfolioId,
				$assetId
			);		

		$r=$this->pdoQuery($query);		

		if($row=$r->fetch()) {
			return $row["assetQuantity"];
		}

		return 0;
	}
	
	private function pdoQuery($query) {
		$r=$this->pdo()->query($query);		
		if ($r===false) Nano\nanoCheck()->checkFailed("pdoQuery: failed to execute query.\nquery:$query");
		return $r;
	}

	function portfolioAssetQuantities($portfolioId) {
		$query=sprintf(
			"SELECT portfolioId,assetId,SUM(assetQuantity) as assetQuantity FROM portfolioAsset
				WHERE
				 portfolioId='%s'
				GROUP BY
					portfolioId,assetId
			",
				$portfolioId
			);		

		$r=$this->pdoQuery($query);				
		$assets=[];

		while ($row=$r->fetch()) {
			$assetId=$row["assetId"];
			$assetQuantity=$row["assetQuantity"];
			$assets[$assetId]=$assetQuantity;
		}

		return $assets;
	}


	function portfolioAddAssetQuantity($portfolioId,$assetId,$quantity,&$market,$isDeposit) {
		Asset\checkAssetId($assetId);

		$query=sprintf(
			"INSERT INTO portfolioAsset 
				(portfolioId,assetId,assetQuantity,opTime,opType)
				VALUES 
				 ('%s','%s',%s,%s,%s)
			",
				$portfolioId,$assetId,$quantity,time(),AssetTradeOperation\Buy
			);				
		
		$r=$this->pdoQuery($query);				

		if ($isDeposit) {
			$this->portfolioLastDepositTime($portfolioId,time());
			$this->portfolioLastDepositQuantity($portfolioId,$quantity);	
		} 
	}

	function portfolioRemoveAssetQuantity($portfolioId,$assetId,$quantity,&$market) {
		Asset\checkAssetId($assetId);
		$originalQuantity=$this->portfolioAssetQuantity($portfolioId,$assetId);

		if ($originalQuantity==0) return;

		$assetQuantity=$originalQuantity-$quantity;

		if ($assetQuantity<0) {
			throw new \Exception("Removing more than remaining in portfolio portfolioId:$portfolioId assetId:$assetId");
		}

		$query=sprintf(
			"INSERT INTO portfolioAsset 
				(portfolioId,assetId,assetQuantity,opTime,opType)
				VALUES
				 ('%s','%s',%s,%s,%s)
			",
				$portfolioId,$assetId,-1*$quantity,time(),AssetTradeOperation\Sell
			);		

		$r=$this->pdoQuery($query);		
	}

	private function portfolioFieldInt($portfolioId,$field,$value=null) {
		if ($value!=null) {
			$query=sprintf(
				"UPDATE portfolio 
					SET $field=$value 
					WHERE
					 portfolioId='%s'					 
				",
					$portfolioId
				);		

			$this->pdoQuery($query);

			return null;
		} else {
			return $this->portfolioRow($portfolioId)[$field];
		}
	}

	private function portfolioRow($portfolioId) {
		$query=sprintf(
		"SELECT * 
			FROM portfolio p
			WHERE 
				p.portfolioId='%s'				
		"
		,$portfolioId
		);		

		$r=$this->pdoQuery($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("porfolio row not found for portfolioId:'$portfolioId'");
		}		
	}

	function boolToSql($v) {
		return $v ? 1 : 0;
	}

	function nullableSql($v) {
		return $v===null ? "NULL" : $v;
	}

	function valueSql($v) {
		return round($v, 10);	
	}
	
	function nullableValueSql($v) {
		return $v===null ? "NULL" : $this->valueSql($v);
	}

	function traderQueueOrder($order) {
		$nextQueueId=$this->traderNextQueueId($order->botArenaId(),$order->traderId());
		$query=sprintf(
		"INSERT INTO assetTradeOrder(
				botArenaId,traderId,queueId,
				assetId,tradeOp,quantity,
				targetQuote,status,done,
				statusChangeBeat,statusChangeTime,queueBeat,
				parentQueueId,notified,doneBeat,
				doneTime,doneQuote
				) VALUES (
				'%s','%s',%s,\n
				'%s',%s,%s,\n
				%s,%s,%s,\n
				%s,%s,%s,\n
				%s,%s,%s,\n
				%s,%s\n
				)
		"
		,
		$order->botArenaId(),$order->traderId(),$nextQueueId,
		$order->assetId(),$order->tradeOp(),$order->quantity(),
		$this->valueSql($order->targetQuote()),$order->status(),$this->boolToSql($order->done()),
		$this->nullableSql($order->statusChangeBeat()),$this->nullableSql($order->statusChangeTime()),$this->nullableSql($order->queueBeat()),
		$this->nullableSql($order->parentQueueId()),$this->boolToSql($order->notified()),$this->nullableSql($order->doneBeat()),
		$this->nullableSql($order->doneTime()),$this->nullableValueSql($order->doneQuote())
		);		

		$r=$this->pdoQuery($query);
	}		

	function traderOrderUpdate($order) {
		$query=sprintf(
		"UPDATE assetTradeOrder
			SET				
				assetId='%s',tradeOp=%s,quantity=%s,
				targetQuote=%s,status=%s,done=%s,
				statusChangeBeat=%s,statusChangeTime=%s,queueBeat=%s,
				parentQueueId=%s,notified=%s,doneBeat=%s,
				doneTime=%s,doneQuote=%s

				WHERE botArenaId='%s' AND traderId='%s' AND queueId=%s
		"
		,
		$order->assetId(),$order->tradeOp(),$order->quantity(),
		$this->valueSql($order->targetQuote()),$order->status(),$this->boolToSql($order->done()),
		$this->nullableSql($order->statusChangeBeat()),$this->nullableSql($order->statusChangeTime()),$this->nullableSql($order->queueBeat()),
		$this->nullableSql($order->parentQueueId()),$this->boolToSql($order->notified()),$this->nullableSql($order->doneBeat()),
		$this->nullableSql($order->doneTime()),$this->nullableValueSql($order->doneQuote()),

		$order->botArenaId(),$order->traderId(),$order->queueId()

		);		

		$r=$this->pdoQuery($query);
	}		

}

?>
