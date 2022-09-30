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
	private $cache=[];

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

	function pdoSettings(&$pdoSettings=null) {
		if ($pdoSettings!=null) {
			$this->pdoSettings=$pdoSettings;
		} else {
			if ($this->pdoSettings==null) {
				throw new \Exception("pdo not yet setup");
			}
		}

		return $this->pdoSettings;
	}


	function marketPollContentMaxAgeSeconds($marketId) {
		return $this->marketField($marketId,"pollContentMaxAgeSeconds");
	}

	function marketMaxHistoryBeats($marketId) {
		return $this->marketField($marketId,"maxHistoryBeats");
	}

	function marketStatsLongBeatMultiplier($marketId) {
		return $this->marketField($marketId,"statsLongBeatMultiplier");
	}
	
	function marketStatsMediumBeatMultiplier($marketId) {
		return $this->marketField($marketId,"statsMediumBeatMultiplier");
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

	function traderMaxHistoryBeats($marketId) {
		return $this->marketFieldInt($marketId,"maxHistoryBeats");
	}

	function traderStatsLongBeatMultiplier($marketId) {
		return $this->marketFieldInt($marketId,"statsLongBeatMultiplier");
	}

	function traderStatsMediumBeatMultiplier($marketId) {
		return $this->marketFieldInt($marketId,"statsMediumBeatMultiplier");
	}


	function traderOrderQueueCacheKey($botArenaId,$traderId) {		
		return sprintf("traderOrderQueue_%s_%s",$botArenaId,$traderId);
	}

	function traderOrderQueue($botArenaId,$traderId) {		

		$key=$this->traderOrderQueueCacheKey($botArenaId,$traderId);

		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {

			$os=[];		
			$query=sprintf("SELECT * FROM assetTradeOrder as o						
								WHERE botArenaId='%s' 
									AND traderId='%s'",$botArenaId,$traderId);

			$r=$this->pdoQuery($query,"traderOrderQueue");

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

			$this->cacheStore($key,$os);
			return $os;
		}
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

		$this->pdoQuery($query,"marketFieldSetInt");
		$this->cacheCleanKey($this->marketRowCacheKey($marketId));

	}

	private function marketFieldSetString($marketId,$field,$value) {
		$query=sprintf(
			"UPDATE market SET $field='$value' WHERE marketId='%s'",
				$marketId);		

		$this->pdoQuery($query,"marketFieldSetString");
		$this->cacheCleanKey($this->marketRowCacheKey($marketId));
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

			$this->pdoQuery($query,"yfMarketFieldInt");

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

			$this->pdoQuery($query,"dsTraderFieldInt");
			$this->cacheCleanKey($this->dsTraderRowCacheKey());
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

			$this->pdoQuery($query,"traderFieldInt");
			$this->cacheCleanKey($this->traderRowCacheKey($botArenaId,$traderId));

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

			$this->pdoQuery($query,"traderFieldDouble");
			$this->cacheCleanKey($this->traderRowCacheKey($botArenaId,$traderId));

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

		$r=$this->pdoQuery($query,"marketBotArenaId");

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

			$this->pdoQuery($query,"dsTraderFieldDouble");

			return null;
		} else {
			return $this->dsTraderRow($botArenaId,$traderId)[$field];
		}
	}

	private function cacheHit($key) {
		return array_key_exists($key,$this->cache);
	}

	private function cacheStore($key,&$value) {
		$this->cache[$key]=$value;
	}

	private function cached($key) {
		if (array_key_exists($key,$this->cache)) {
			Nano\nanoPerformance()->track("persistence.cached");
			//Nano\nanoPerformance()->track("persistence.cached_$key");
			$v=$this->cache[$key];
			//Nano\nanoPerformance()->track("persistence.cached_$key");
			Nano\nanoPerformance()->track("persistence.cached");
			return $v;
		}
		return false;
	}


	private function marketRowCacheKey($marketId) {
		return "marketRow_$marketId";
	}

	private function marketRow($marketId) {
		$key=$this->marketRowCacheKey($marketId);

		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {
			$query=sprintf(
			"SELECT * FROM market WHERE marketId='%s'",
				$marketId);		

			$r=$this->pdoQuery($query,"marketRow");
			
			if ($row=$r->fetch()) {
				$this->cacheStore($key,$row);
				return $row;
			} else {
				Nano\nanoCheck()->checkFailed("marketStats row not found");
			}		

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

		$r=$this->pdoQuery($query,"yfMarketRow");
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("yfMarket row not found");
		}		
	}




	private function dsTraderRowCacheKey($botArenaId,$traderId) {
		return sprintf("dsTraderRowCacheKey_%s_%s",$botArenaId,$traderId);
	}

	private function dsTraderRow($botArenaId,$traderId) {
		$key=$this->dsTraderRowCacheKey($botArenaId,$traderId);
		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {

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

			$r=$this->pdoQuery($query,"dsTraderRow");
			
			if ($row=$r->fetch()) {
				$this->cacheStore($key,$row);
				return $row;
			} else {
				Nano\nanoCheck()->checkFailed("dsTrader row not found");
			}		
		}
	}

	private function traderRowCacheKey($botArenaId,$traderId) {
		return sprintf("traderRow_%s_%s",$botArenaId,$traderId);
	}

	private function traderRow($botArenaId,$traderId) {

		$key=$this->traderRowCacheKey($botArenaId,$traderId);

		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {
		
			$query=sprintf(
			"SELECT * 
				FROM marketTrader t
				WHERE 
					t.traderId='%s' AND
					t.botArenaId='%s'
			"
			,$traderId
			,$botArenaId);		

			$r=$this->pdoQuery($query,"traderRow");
			
			if ($row=$r->fetch()) {
				$this->cacheStore($key,$row);
				return $row;
			} else {
				Nano\nanoCheck()->checkFailed("trader row not found for botArenaId:'$botArena' traderId:'$traderId'");
			}		

		}
	}


	function dsTraderWaitBeats($botArenaId,$traderId,$waitBeats=null) {
		return $this->dsTraderFieldInt($botArenaId,$traderId,"waitBeats",$waitBeats);
	}

	function dsTraderPhase($botArenaId,$traderId,$phase=null) {
		return $this->dsTraderFieldInt($botArenaId,$traderId,"phase",$phase);
	}

	function dsTraderStartBeat($botArenaId,$traderId,$startBeat=null) {
		return $this->dsTraderFieldInt($botArenaId,$traderId,"startBeat",$startBeat);	
	}

	function dsTraderMaxAssetPercentage($botArenaId,$traderId,$maxAssetPercentage=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"maxAssetPercentage",$maxAssetPercentage);	
	}

	function dsTraderWaitBeatsForAssetRepeat($botArenaId,$traderId,$waitBeatsForAssetRepeat=null) {
		return $this->dsTraderFieldInt($botArenaId,$traderId,"waitBeatsForAssetRepeat",$waitBeatsForAssetRepeat);	
	}


	function dsTraderAssetShortTrendLowCut($botArenaId,$traderId,$assetShortTrendLowCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"assetShortTrendLowCut",$assetShortTrendLowCut);	
	}

	function dsTraderAssetMediumTrendLowCut($botArenaId,$traderId,$assetMediumTrendLowCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"assetMediumTrendLowCut",$assetMediumTrendLowCut);	
	}

	function dsTraderMarketShortTrendLowCut($botArenaId,$traderId,$marketShortTrendLowCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"marketShortTrendLowCut",$marketShortTrendLowCut);	
	}

	function dsTraderMarketMediumTrendLowCut($botArenaId,$traderId,$marketMediumTrendLowCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"marketMediumTrendLowCut",$marketMediumTrendLowCut);	
	}

	function dsTraderBuyCicleCut($botArenaId,$traderId,$buyCicleCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"buyCicleCut",$buyCicleCut);	
	}

	function dsTraderSellCicleCut($botArenaId,$traderId,$sellCicleCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"sellCicleCut",$sellCicleCut);	
	}

	function dsTraderMaxBuySuggestions($botArenaId,$traderId,$maxBuySuggestions=null) {
		return $this->dsTraderFieldInt($botArenaId,$traderId,"maxBuySuggestions",$maxBuySuggestions);	
	}

	function dsTraderBuyLimitFactor($botArenaId,$traderId,$buyLimitFactor=null) {
		return $this->dsTraderFieldDouble($botArenaId,$traderId,"buyLimitFactor",$buyLimitFactor);	
	}

	function worldBotArenas() {		
		$r=$this->pdoQuery("SELECT * FROM botArena WHERE enabled=1","worldBotArenas");
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
					,$botArenaId,$traderId),"traderNextQueueId");

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
	
	function portfolioAssetIdsCacheKey($portfolioId) {
		return "portfolioAssetIds_$portfolioId";
	}

	function portfolioAssetIds($portfolioId=null) {		
		
		$key=$this->portfolioAssetIdsCacheKey($portfolioId);

		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {
			$query=sprintf(
				"SELECT DISTINCT(assetId) FROM portfolioAsset
					WHERE
					 portfolioId='%s'
				",
					$portfolioId
				);		

			$r=$this->pdoQuery($query,"portfolioAssetIds");
			
			$assets=[];

			while ($row=$r->fetch()) {
				$assets[]=$row["assetId"];
			}
			$this->cacheStore($key,$assets);
			return $assets;
		}
	}


	function portfolioAssetQuantityCacheKey($portfolioId,$assetId) {
		return sprintf("portfolioAssetQuantity_%s_%s",$portfolioId,$assetId);
	}

	function portfolioAssetQuantity($portfolioId,$assetId) {		
		$key=$this->portfolioAssetQuantityCacheKey($portfolioId,$assetId);

		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {
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

			$r=$this->pdoQuery($query,"portfolioAssetQuantity");		

			if($row=$r->fetch()) {
				$v=$row["assetQuantity"];
				$this->cacheStore($key,$v);
				return $v;
			}
		}

		return 0;
	}

	private function pdoQuery($query,$queryName=null) {
		$infoQuery=$queryName!=null ? $queryName : $query;
		Nano\nanoPerformance()->track("persistence.pdoQuery");
		Nano\nanoPerformance()->track("persistence.pdoQuery.$infoQuery");
		$r=$this->pdo()->query($query);		
		Nano\nanoPerformance()->track("persistence.pdoQuery");
		Nano\nanoPerformance()->track("persistence.pdoQuery.$infoQuery");

		if ($r===false) Nano\nanoCheck()->checkFailed("pdoQuery: failed to execute query.\nquery:$query");
		return $r;
	}

	function portfolioAssetQuantitiesCacheKey($portfolioId) {
		return "portfolioAssetQuantities_$portfolioId";
	}

	function portfolioAssetQuantities($portfolioId) {
		$key=$this->portfolioAssetQuantitiesCacheKey($portfolioId);

		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {

			$query=sprintf(
				"SELECT portfolioId,assetId,SUM(assetQuantity) as assetQuantity FROM portfolioAsset
					WHERE
					 portfolioId='%s'
					GROUP BY
						portfolioId,assetId
				",
					$portfolioId
				);		

			$r=$this->pdoQuery($query,"portfolioAssetQuantities");				
			$assets=[];

			while ($row=$r->fetch()) {
				$assetId=$row["assetId"];
				$assetQuantity=$row["assetQuantity"];
				$assets[$assetId]=$assetQuantity;
			}

			$this->cacheStore($key,$assets);
			return $assets;
		}
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
		
		$r=$this->pdoQuery($query,"portfolioAddAssetQuantity");				

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

		$r=$this->pdoQuery($query,"portfolioRemoveAssetQuantity");		
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

			$this->pdoQuery($query,"portfolioFieldInt");

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

		$r=$this->pdoQuery($query,"portfolioRow");
		
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

	function cacheCleanKey($key) {
		if (array_key_exists($key,$this->cache)) {
			unset($this->cache[$key]);
		}		
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

		$r=$this->pdoQuery($query,"traderQueueOrder");

		$this->cacheCleanKey($this->traderOrderQueueCacheKey($order->botArenaId(),$order->traderId()));

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

		$r=$this->pdoQuery($query,"traderOrderUpdate");

		$this->cacheCleanKey($this->traderOrderQueueCacheKey($order->botArenaId(),$order->traderId()));

	}		


	function infinitePositive() {
		return 1000000000;
	}

	function infiniteNegative() {
		return -1000000000;
	}	

	private function schema() {
		return $this->pdoSettings()->database();
	}

	private function tableExists($tableName) {

		$schema=$this->schema();

		$query=sprintf("
			SELECT COUNT(TABLE_NAME) as count
			FROM 
			   information_schema.TABLES 
			WHERE 
			   TABLE_SCHEMA LIKE '$schema' AND 
				TABLE_TYPE LIKE 'BASE TABLE' AND
				TABLE_NAME = '$tableName';
		");

		$r=$this->pdoQuery($query,"tableExists");
		$row=$r->fetch();

		return $row["count"]!=0;
	}

	private function tableHasRows($tableName) {
		$query=sprintf(
				"SELECT COUNT(*) as count FROM $tableName");				

		$r=$this->pdoQuery($query,"tableHasRows");
		
		$row=$r->fetch();
		return $row["count"]!=0;
	}

	private function msRowMarketStatsCacheKey($marketId,$marketStatsId) {
		return "msRowMarketStats_$marketId"."_$marketStatsId";
	}

	private function msRowMarketStats($marketId,$marketStatsId) {

		$key=$this->msRowMarketStatsCacheKey($marketId,$marketStatsId);

		if ($this->cacheHit($key)) {
			return $this->cached($key);
		} else {

			$query=sprintf(
			"SELECT * FROM marketStats
				WHERE 
					marketId='$marketId' AND 
					marketStatsId='$marketStatsId' 
				");		

			$r=$this->pdoQuery($query,"msRowMarketStats");
			
			if ($row=$r->fetch()) {
				$this->cacheStore($key,$row);
				return $row;
			} else {
				Nano\nanoCheck()->checkFailed("marketStats: statsTableHead:$statsTableHead msg: row not found");
			}		
		}
	}

	private function msFieldMarketStats($marketId,$marketStatsId,$field) {
		return $this->msRowMarketStats($marketId,$marketStatsId)[$field];
	}

	private function msFieldMarketStatsSetInt($marketId,$marketStatsId,$field,$value) {		
	
		$query=sprintf(
			"UPDATE marketStats SET $field=$value
  			 WHERE 
				marketId='$marketId' AND 
				marketStatsId='$marketStatsId' 

			","msFieldMarketStatsSetInt");		

		$this->pdoQuery($query,"msFieldMarketStatsSetInt");

		$this->cacheCleanKey($this->msRowMarketStatsCacheKey($marketId,$marketStatsId));
	}

	function msSynchedBeat($marketId,$marketStatsId,$synchedBeat=null) {
		if ($synchedBeat!=null) {
			$this->msFieldMarketStatsSetInt($marketId,$marketStatsId,"synchedBeat",$synchedBeat);
		}
		return $this->msFieldMarketStats($marketId,$marketStatsId,"synchedBeat");
	}
	
	function msMaxHistoryBeats($marketId,$marketStatsId,$maxHistoryBeats=null) {
		if ($maxHistoryBeats!=null) {
			$this->msFieldMarketStatsSetInt($marketId,$marketStatsId,"maxHistoryBeats",$maxHistoryBeats);
		}
		return $this->msFieldMarketStats($marketId,$marketStatsId,"maxHistoryBeats");
	}

	function msBeatMultiplier($marketId,$marketStatsId,$beatMultiplier=null) {
		if ($beatMultiplier!=null) {
			$this->msFieldMarketStatsSetInt($marketId,$marketStatsId,"beatMultiplier",$beatMultiplier);
		}
		return $this->msFieldMarketStats($marketId,$marketStatsId,"beatMultiplier");
	}

	function msEndBeat($marketId,$marketStatsId,$endBeat=null) {
		if ($endBeat!=null) {
			$this->msFieldMarketStatsSetInt($marketId,$marketStatsId,"endBeat",$endBeat);
		}
		return $this->msFieldMarketStats($marketId,$marketStatsId,"endBeat");
	}


	function cacheClean() {
		$this->cache=[];
	}

	function afterRunBeat() {
		$this->cacheClean();
	}

	function afterTradeOne() {
		$this->cacheClean();	
	}
}


?>
