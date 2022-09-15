<?php
namespace xnan\Trurl\Horus\MarketTrader;
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
use xnan\Trurl\Horus\WorldSettings;
use xnan\Trurl\Horus\Portfolio;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

AssetTradeOperation\Functions::Load;
AssetTradeStatus\Functions::Load;

class Functions { const Load=1; }

abstract class MarketTrader {
	var $portfolioId=-1;
	var $botArenaId;
	var $traderId;
	var $market;
	var $nextQueueId=1;
	var $autoApprove=false;
	var $minFlushBeats=1;
	var $settingBuyUnits=1;
	var $settingBuyMinimum=0.1;
	var $minEarn=5;
	var $notificationsEnabled=false;	
	var $autoCancelBuyBeats=8;
	var $textFormater;
	var $traderTitle;
	
	var $dailyWaitFromMarketOpen=0;
	var $dailyWaitFromMarketClose=0;
	var $marketBroker=null;
	var $openTabToBroker=false;
	var $telegramChatId="1639383990";

	function __construct($botArenaId,$traderId,&$portfolioId) {		
		$this->textFormatter=Nano\newTextFormatter();
		$this->botArenaId=$botArenaId;
		$this->traderId=$traderId;
		$this->portfolioId=$portfolioId;
		$this->traderTitle=$traderId;		
		$this->setupPortfolio();
		$this->setupSettings();
	}

	private function pdo() {
		return BotWorld\BotWorld::instance()->pdo();
	}

	private function setupPortfolio() {
		$query=sprintf("SELECT * FROM portfolio WHERE portfolioId='%s'",$this->portfolioId);

		$r=$this->pdo()->query($query);

		if ($row=$r->fetch()) {
			$this->portfolio=new Portfolio\Portfolio($row["portfolioId"],$row["lastDepositTime"],$row["lastDepositQuantity"]);		
		}		
	}

	function setupSettings() {
		$s=BotWorld\BotWorld::instance()->settings();		

		$s->registerChangeListener("marketTrader.traderTitle",$this);
		$s->registerChangeListener("marketTrader.minFlushBeats",$this);
		$s->registerChangeListener("marketTrader.settingBuyUnits",$this);
		$s->registerChangeListener("marketTrader.settingBuyMinimum",$this);
		$s->registerChangeListener("marketTrader.minEarn",$this);
		$s->registerChangeListener("marketTrader.autoCancelBuyBeats",$this);
		$s->registerChangeListener("marketTrader.dailyWaitFromMarketOpen",$this);
		$s->registerChangeListener("marketTrader.dailyWaitFromMarketClose",$this);
		$s->registerChangeListener("marketTrader.telegramChatId",$this);
		$s->registerChangeListener("marketTrader.notificationsEnabled",$this);
		$s->registerChangeListener("marketTrader.recover",$this);
	}

	function onSettingsChange($key,$params) {		

		if ($params["traderId"]==$this->traderId() &&			
			$params["marketId"]==$this->market()->marketId()
			) {						
			if ($key=="marketTrader.traderTitle") { $this->traderTitle=$params["settingsValue"]; }			
			if ($key=="marketTrader.minFlushBeats") { $this->minFlushBeats=$params["settingsValue"]; }			
			if ($key=="marketTrader.settingBuyUnits") { $this->settingBuyUnits=$params["settingsValue"]; }			
			if ($key=="marketTrader.settingBuyMinimum") { $this->settingBuyMinimum=$params["settingsValue"]; }			
			if ($key=="marketTrader.minEarn") { $this->minEarn=$params["settingsValue"]; }			
			if ($key=="marketTrader.autoCancelBuyBeats") { $this->autoCancelBuyBeats=$params["settingsValue"]; }						
			if ($key=="marketTrader.dailyWaitFromMarketOpen") { $this->dailyWaitFromMarketOpen=$params["settingsValue"]; }			
			if ($key=="marketTrader.dailyWaitFromMarketClose") { $this->dailyWaitFromMarketClose=$params["settingsValue"]; }			
			if ($key=="marketTrader.telegramChatId") {  $this->telegramChatId=$params["settingsValue"]; }			
			if ($key=="marketTrader.notificationsEnabled") {  $this->notificationsEnabled=$params["settingsValue"]; }
			if ($key=="marketTrader.recover" && $params["settingsValue"]=="true") { $this->traderRecover(); }			


		}
	}

	function textFormatter() {
		return $this->textFormatter;
	}
	
	function traderSettingsAsCsv() {
		$ds=new DataSet\DataSet(["settingsKey","settingsDescription","settingsValue"]);
		
		$traderId=$this->traderId();
		$lastDepositDate=new \DateTime();
		$lastDepositDate->setTimestamp($this->portfolio()->lastDepositTime());

		$ds->addRow(["traderId","ID Bot",$this->traderId]);
		$ds->addRow(["traderTitle","Título del Bot",$this->textFormatter()->formatString($this->traderTitle,"settingsKey=marketTrader.traderTitle&traderId=$traderId")]);
		$ds->addRow(["autoApprove","Auto aprobar órdenes",$this->autoApprove ? "true":"false"]);
		$ds->addRow(["minFlushBeats","Espera antes de procesar orden",$this->textFormatter()->formatInt($this->minFlushBeats,"settingsKey=marketTrader.minFlushBeats&traderId=$traderId")]);
		$ds->addRow(["settingBuyUnits","Unidades de compra (redondeo)",$this->textFormatter()->formatDecimal($this->settingBuyUnits,"","settingsKey=marketTrader.settingBuyUnits&traderId=$traderId")]);
		$ds->addRow(["settingBuyMinimum","Minimo de compra (cantidad)",$this->textFormatter()->formatDecimal($this->settingBuyMinimum,"","settingsKey=marketTrader.settingBuyMinimum&traderId=$traderId")]);
		$ds->addRow(["minEarn","Ganancia mínima esperada (monto)",$this->textFormatter()->formatDecimal($this->minEarn,"","settingsKey=marketTrader.minEarn&traderId=$traderId")]);
		$ds->addRow(["notificationsEnabled","Notifaciones habilitadas",$this->textFormatter()->formatBool($this->notificationsEnabled,"settingsKey=marketTrader.notificationsEnabled&traderId=$traderId")]);
		$ds->addRow(["nextQueueId","ID orden próximo",$this->nextQueueId]);
		$ds->addRow(["telegramChatId","Chat ID de telegram",$this->textFormatter()->formatString($this->telegramChatId,"settingsKey=marketTrader.telegramChatId&traderId=$traderId")]);
		$ds->addRow(["isSuspended","Suspendido",$this->textFormatter->formatBool($this->isSuspended())]);
		$ds->addRow(["autoCancelBuyBeats","Pulsos de espera hasta cancelar compras aprobadas",$this->textFormatter()->formatInt($this->autoCancelBuyBeats,"settingsKey=marketTrader.autoCancelBuyBeats&traderId=$traderId")]);
		$ds->addRow(["porfolio.lastDepositQuantity","Cantidad de último depósito",$this->portfolio()->lastDepositQuantity()]);
		$ds->addRow(["porfolio.lastDepositTime","Fecha de último depósito",date_format($lastDepositDate,'Y-m-d H:i:s')]);
		$ds->addRow(["nextQueueId","ID orden próximo",$this->nextQueueId]);
		//$ds->addRow(["randomInt","Valor al azar de prueba",random_int(1,1000)]);

		$this->addTraderCustomSettings($ds);
		return $ds->toCsvRet();		
	}

	function traderReset() {
		exit("traderReset: deferred");
		$this->orderQueue()->reset();
		$this->nextQueueId=1;
	}

	function telegramChatId($chatId=null) {
		if ($chatId!=null) $this->telegramChatId=$chatId;
		return $this->telegramChatId;
	}

 	function traderTitle($traderTitle=null) {
 		if ($traderTitle!=null) $this->traderTitle=$traderTitle;
 		return $this->traderTitle;
 	}

 	function minEarn($minEarn=null) {
 		if ($minEarn!=null) $this->minEarn=$minEarn;
 		return $this->minEarn;
 	}

	function suspend() {
		$query=sprintf("UPDATE marketTrader 
				SET isSuspended=1
				WHERE
					botArenaId='%s' AND
					traderId='%s'
				",			
				$this->botArenaId(),
				$this->traderId());

		$this->pdo()->query($query);			
	}

	function resume() {
		
		$query=sprintf("UPDATE marketTrader 
				SET isSuspended=0
				WHERE
					botArenaId='%s' AND
					traderId='%s'
				",			
				$this->botArenaId(),
				$this->traderId());
		
		$this->pdo()->query($query);			

	}

	function isSuspended() {		
		$query=sprintf("SELECT * FROM marketTrader 
				WHERE
					botArenaId='%s' AND
					traderId='%s'
				",			
				$this->botArenaId(),
				$this->traderId());
		
		$r=$this->pdo()->query($query);			
		if ($row=$r->fetch()) {
			return $row["isSuspended"]==1;
		}	
		Nano\nanoCheck()->checkFailed(sprintf("marketTrader not found: '%s.%s'",$this->botArenaId(),$this->traderId()));
	}

	function textFormater() {
		return $this->textFormatter;
	}	

	function autoCancelApproved() {
		$this->orderQueue()->markChanged();
		
		if ($this->autoCancelBuyApprovedBeats>0) {
			foreach($this->orderQueue()->values() as &$order) {
				if (!$order->done() && $order->status()==AssetTradeStatus\Approved) {
					$order->status(AssetTradeStatus\Approved);	
					$order->statusChangeBeat($this->market()->beat());
					$order->statusChangeTime(time());
				}
			}
		}
	}

	function addTraderCustomSettings($ds) {
	}

	function traderId() {
		return $this->traderId;
	}

	function setupMarket(&$market) {
		$this->market=$market;
	}

	function setupAutoApprove($autoApprove) {
		$this->autoApprove=$autoApprove;
	}

	function market() {
		return $this->market;
	}

	abstract function trade(&$market);

	function acceptOrder($orderId) {

		$query=sprintf("UPDATE assetTradeOrder 
				SET status=%s, 
					statusChangeBeat=%s,
					statusChangeTime=%s
				WHERE
					botArenaId='%s' AND
					traderId='%s' AND
					queueId=%s
				",
			
				AssetTradeStatus\Approved,	
				$this->market()->beat(),
				time(),
				$this->botArenaId(),
				$this->traderId(),
				$orderId);
		
		$this->pdo()->query($query);			

	}

	function cancelOrder($orderId) {
		$query=sprintf("UPDATE assetTradeOrder 
				SET status=%s, 
					statusChangeBeat=%s,
					statusChangeTime=%s
				WHERE
					botArenaId='%s' AND
					traderId='%s' AND
					done=0 AND
					(queueId=%s OR
						parentQueueId=%s)
					
				",
			
				AssetTradeStatus\Cancelled,	
				$this->market()->beat(),
				time(),
				$this->botArenaId(),
				$this->traderId(),
				$orderId,
				$orderId);		
		
		$this->pdo()->query($query);
	}

	function notifyOrders(&$market) {		
		foreach($this->orderQueue() as &$order) {
			$this->notifyTelegram($order);
		}
	}

	function flushOrders(&$market) {
		//print_r($this->orderQueue);
		//print $this->queueCancelledAsCsv();

		foreach($this->orderQueue() as &$order) {


			$flushAllowed=$this->minFlushBeats < ($market->get()->beat() - $order->queueBeat()) ? true:false;
			//PRINT "\nACA1 flushAllowed:$flushAllowed autoApprove:".($this->autoApprove)." status ".($order->status())."\n";

			if (!$order->done() && $flushAllowed && $order->status()==AssetTradeStatus\Approved) {			

				$quote=$market->get()->assetQuote($order->assetId());
				$doOp=false;

				$quantity=0;
				$doable=$this->orderIsDoable($order,$quantity,true);

				if (!$doable && $order->tradeOp()==AssetTradeOperation\Buy && $this->autoCancelBuyBeats>0 && 
					$market->get()->beat()-$order->statusChangeBeat() > $this->autoCancelBuyBeats) {
					//print sprintf("%%%%%%%%%%%%%%%%%%%% ORDERCANCELLED %s %s!\n",$market->get()->beat(),$order->statusChangeBeat());
					$this->cancelOrder($order->queueId());
				}

				if ($order->tradeOp()==AssetTradeOperation\Buy ) {
					if ($doable) {
						$doOp=true;
						//print sprintf("%s OK buy order:%s %s<br>",$this->traderId,$order->queueId(),$order->assetId(),$quote->sellQuote(),$order->targetQuote());
					} else {
					//	print sprintf("%s FAIL buy order:%s %s<br>",$this->traderId,$order->queueId(),$order->assetId(),$quote->sellQuote(),$order->targetQuote());
					}
				}

				if ($order->tradeOp()==AssetTradeOperation\Sell) {					
					if ($doable) {
						$doOp=true;
						//print sprintf("%s OK sell order:%s %s<br>",$this->traderId,$order->queueId(),$order->assetId(),$quote->sellQuote(),$order->targetQuote());
					} else {
//						print sprintf("%s FAIL sell order:%s %s not doable<br>",$this->traderId,$order->queueId(),$order->assetId(),$quote->sellQuote(),$order->targetQuote());
					}
				}

				if ($doOp) {
					$doneQuote=$market->get()->assetTrade($this->portfolio,$order->assetId(),$order->tradeOp(),$quantity);	
					if (!($doneQuote===false)) {
						$order->done(true);
						$order->doneQuote($doneQuote);
						$order->doneBeat($this->market()->beat());
						$order->doneTime(time());						
					} else {
						printf("########################### FAILTRADE! %s op:%s \n",$order->queueId(),$order->tradeOp());
					}
				}
			}

		}
	}

	function settingBuyUnits($buyUnits=null) {
		if ($buyUnits!=null) $this->settingBuyUnits=$buyUnits;
		return $this->settingBuyUnits;
	}

	function settingBuyMinimum($buyMinimum=null) {
		if ($buyMinimum!=null) $this->settingBuyUnits=$buyMinimum;
		return $this->settingBuyMinimum;
	}

	function buyAtLimit($market,$assetId,$limitMultiplier) {
		$buyQuote=$market->get()->assetQuote($assetId)->buyQuote();
		$buyQuote=$buyQuote*$limitMultiplier;
		return $buyQuote;	
	}

	function sellAtLimit($market,$assetId,$limitMultiplier) {
		$sellQuote=$market->get()->assetQuote($assetId)->sellQuote();
		$sellQuote=$sellQuote*$limitMultiplier;
		return $sellQuote;	
	}

	function nextQueueId() {
		return $this->nextQueueId++;
	}

	function defaultStatus() {
		$status=$this->autoApprove ? AssetTradeStatus\Approved : AssetTradeStatus\Suggested;
		return $status;
	}

	function orderIsDoable($order,&$quantity,$showInfo=false) {		
		$doable = true;

		$doneOk=!$order->done();
		$doable=$doneOk;

		$statusApproved=$doable && 
			($order->status()==AssetTradeStatus\Approved ||
			 $order->status()==AssetTradeStatus\Suggested);

		$doable = $doable && $statusApproved;
		

		$buyQuoteLimitOk= $doable && 
			( $order->tradeOp()!=AssetTradeOperation\Buy ||
			 $order->targetQuote()>=$this->market()->assetQuote($order->assetId())->buyQuote() );

		$doable = $doable && $buyQuoteLimitOk;

		
		$sellQuoteLimitOk= $doable && 
			( $order->tradeOp()!=AssetTradeOperation\Sell ||
			 $order->targetQuote()<=$this->market()->assetQuote($order->assetId())->sellQuote());

		$doable = $doable && $sellQuoteLimitOk;


		$waitBeatsOk= $doable && ($this->minFlushBeats < ($this->market()->beat() - $order->queueBeat()) );

		$doable = $doable && $waitBeatsOk;
		

		$parentDone = $order->parentQueueId()==null || ($this->findOrderById($order->parentQueueId()))->done();
		$doable = $doable && $parentDone;


		$quantity=0;
		if ($order->tradeOp()==AssetTradeOperation\Buy) $quantity=min($order->quantity(),$this->market()->maxBuyQuantity($this->portfolio,$order->assetId()));

		if ($order->tradeOp()==AssetTradeOperation\Sell) $quantity=min($order->quantity(),$this->market()->maxSellQuantity($this->portfolio,$order->assetId()));

		$quantityOk=$quantity>0 && $quantity==$order->quantity();
		
		$doable = $doable && $quantityOk;
			
		if (!$doable && $showInfo) {

		$info=array(
			"traderId"=>$this->traderId(),
			"orderId"=>$order->queueId(),
			"assetId"=>$order->assetId(),			
			"tradeOp"=>AssetTradeOperation\toCanonical($order->tradeOp()),
			"parentQueueId"=>$order->parentQueueId(),
			"doneOk"=>$this->textFormatter()->formatBool($doneOk),
			"statusApproved"=>$this->textFormatter()->formatBool($statusApproved),
			"targetQuote"=>$order->targetQuote(),
			"BuyQuote"=>$this->market()->assetQuote($order->assetId())->buyQuote(),
			"buyQuoteLimitOk"=>$this->textFormatter()->formatBool($buyQuoteLimitOk),
			"sellQuoteLimitOk"=>$this->textFormatter()->formatBool($sellQuoteLimitOk),
			"waitBeatsOk"=>$this->textFormatter()->formatBool($waitBeatsOk),
			"parentDone"=>$this->textFormatter()->formatBool($parentDone),
			"quantityOk"=>$this->textFormatter()->formatBool($quantity),
			"porfolioQuantity"=> $this->portfolio()->assetQuantity($order->assetId()),
			"maxBuyQuantity"=> $this->market()->maxBuyQuantity($this->portfolio,$order->assetId()),
			"maxSellQuantity"=> $this->market()->maxSellQuantity($this->portfolio,$order->assetId())
		);

//			if (!$quantityOk) {				
//				print_r($info);				
//			}

		}

		return $doable;
	}

	function queueOrder($assetId,&$tradeOp,$quantity,$quote,$status=null,$parentQueueId=null) {
		$this->orderQueue()->markChanged();
		
		if ($status==null) $status=$this->defaultStatus();
		$order=new AssetTradeOrder\AssetTradeOrder();
		$order->traderId($this->traderId());
		$order->queueId($this->nextQueueId());
		$order->assetId($assetId);
		$order->tradeOp($tradeOp);
		$order->quantity($quantity);
		$order->targetQuote($quote);
		$order->status($status);
		$order->done(false);		
		$order->parentQueueId($parentQueueId);
		$order->statusChangeBeat($this->market()->beat());
		$order->queueBeat($this->market()->beat());
		$this->orderQueue()->insert($order);
		
		$queueId=$order->queueId();

		return $queueId;
	}

	function setupNotificationsEnabled($enabled) {
		$this->notificationsEnabled=$enabled;
	}

	function notificationsEnabled() {
		return $this->notificationsEnabled;
	}

	function notifyTelegram(&$order) {		
		$this->orderQueue()->markChanged();
		
		if ($this->notificationsEnabled()) {			
			$quantity=0;
			if (!$order->notified() && $this->orderIsDoable($order,$quantity,false) && !$order->done() && $order->status()==AssetTradeStatus\Suggested) {
				$opMsg=AssetTradeOperation\toCanonical($order->tradeOp());
				$chatMsg2="[text](http://example.com)";

				$marketId=$this->market()->marketId();
				$assetId=$order->assetId();
				$quantity=$order->quantity();
				$targetQuote=$order->targetQuote();
				$traderUrl=$this->traderUrl();				
				$chatMsg = <<<MARKDOWN
**$opMsg $marketId.$assetId**
Cantidad: $quantity
Precio de Compra: $targetQuote
\[visitar]($traderUrl)
MARKDOWN;		
				//print "NOTIFENABLccc:".$this->notificationsEnabled()."<br>";
				$this->telegram($chatMsg);			
				$order->notified(true);
			}
		}
	}

	function traderUrl() {
		return sprintf("%s/BotArenaWeb/index.php",Trurl\domain());
	}

	function pendingBuySuggestionsCount() {
		$count=0;
		foreach($this->orderQueue()->values() as &$order) {
			if  (!$order->done() && $order->tradeOp()==AssetTradeOperation\Buy) {
				if ($order->status()==AssetTradeStatus\Suggested) {
					++$count;
				}				
			}
		}
		return $count;
	}

	function findPendingOrder($parentQueueId,$assetId,$tradeOp) {
		$retOrder=null;
		foreach($this->orderQueue()->values() as &$order) {
			if  (!$order->done() && $order->parentQueueId()==$parentQueueId  && $order->assetId()==$assetId && $order->tradeOp()==$tradeOp) {
				if ($order->status()!=AssetTradeStatus\Rejected && $order->status()!=AssetTradeStatus\Cancelled && $order->status()!=AssetTradeStatus\Approved) {
					$retOrder=$order;	
					break;
				}				
			}
		}
		return $retOrder;
	}

	function findOrderById($queueId) {
		$retOrder=null;
		foreach($this->orderQueue()->values() as &$order) {
			if  ($order->queueId()==$queueId) {
				$retOrder=$order;	
				break;
			}				
		}
		return $retOrder;
	}

	function updateOrder($parentQueueId,&$order,$assetId,&$tradeOp,$quantity,$quote,$status=null,$newParentQueueId=null) {
		$this->orderQueue()->markChanged();
		
		$order->quantity($quantity);
		$order->targetQuote($quote);
		$order->status($status);
		$order->parentQueueId($newParentQueueId);
		$order->statusChangeBeat($this->market()->beat());
		return $order->queueId();		
	}

	function queueOrUpdateOrder($parentQueueId,$assetId,$tradeOp,$quantity,$quote,$status=null,$newParentQueueId=null) {		
		$this->orderQueue()->markChanged();
		
		$order=$this->findPendingOrder($parentQueueId,$assetId,$tradeOp);
		//echo sprintf("find %s,$assetId,$tradeOp,parent:$parentQueueId:%s<br>",$this->traderId() ,($order!=null ? "yes": "no"));
		$queueId=null;
		if ($order==null) {
			$queueId=$this->queueOrder($assetId,$tradeOp,$quantity,$quote,$status,$newParentQueueId);
		} else {
			$queueId=$this->updateOrder($parentQueueId,$order,$assetId,$tradeOp,$quantity,$quote,$status,$newParentQueueId);
		}
		return $queueId;
	}

	function orderQueueByStatus($status,$filterOrdersWithParentSuggested) {
		$ret=[];

		foreach ($this->orderQueue() as $order) {			
			if ($order->status()==$status) {
				$parentOrder=$order->parentQueueId()!=null ? $this->findOrderById($order->parentQueueId()) : null;
				if (!$filterOrdersWithParentSuggested || $parentOrder==null || $parentOrder->status()!=AssetTradeStatus\Suggested) {
					$ret[]=$order;	
				}				
			}
		}
		return $ret;
	}

	function orderQueueToCanonical() {
		$ret="";
		$doneCount=0;
		foreach ($this->orderQueue()->values() as $order) {
			if (!$order->done()) {
				if (strlen($ret)>0) $ret.=" ";
				$ret.=sprintf("#%s.t%s.%s %s:%s at %s(%s) parent:%s",$order->queueId(),$order->queueBeat(),AssetTradeOperation\toCanonical($order->tradeOp()),$order->assetId(),$order->quantity(),$order->targetQuote(),AssetTradeStatus\toCanonical($order->status()),$order->parentQueueId() );				
			}  else {
				++$doneCount;
			}
		}
		return sprintf("orderQueue:[%s] doneCount:%s",$ret,$doneCount);
	}

	function hasOrderQueueByStatusAndAssetId($status,$assetId) {
		foreach ($this->orderQueue()->values() as $order) {
			if ($order->status()==$status && $order->assetId()==$assetId) return true;
		}
		return false;
	}

	function botSuggestions() {		
		return $this->orderQueueByStatus(AssetTradeStatus\Suggested,true);
	}

	function botSuggestionsAsCsv() {
		$dsSuggestions=new DataSet\DataSet(["index","traderId","queueId","assetId","tradeOp","quantity","limitQuote","currentQuote","status","doable","statusChangeBeat","statusChangeTime"]);
		
		$index=1;

		//$this->market()->textFormater()->textFormat("text"); // TEST REMOVER
		foreach($this->botSuggestions() as $s) {
			$quantity=0;
			$doable=$this->orderIsDoable($s,$quantity,false);
			
			$quote=$this->market()->assetQuote($s->assetId());
			$currentQuote=0;			
			if ($s->tradeOp()==AssetTradeOperation\Buy) $currentQuote=$quote->buyQuote();
			if ($s->tradeOp()==AssetTradeOperation\Sell) $currentQuote=$quote->sellQuote();

			$dsSuggestions->addRow([
				$index,
				$s->traderId(),
				$s->queueId(),
				$s->assetId(),
				AssetTradeOperation\toCanonical($s->tradeOp()),
				$this->market()->textFormater()->formatQuantity($s->quantity()),
				$this->market()->textFormater()->formatDecimal($s->targetQuote()),
				$this->market()->textFormater()->formatDecimal($currentQuote),
				AssetTradeStatus\toCanonical($s->status()),
				$doable ? "true":"false",
				$s->statusChangeBeat(),
				Nano\nanoTextFormatter()->dateLegend($s->statusChangeTime())
			]);
			++$index;
		} 

		return $dsSuggestions->toCsvRet();
	}

	function queuePendingAsCsv() {
		$ds=new DataSet\DataSet(["index","traderId","queueId","assetId","tradeOp","quantity","limitQuote","currentQuote","waitBeats","status","doable","statusChangeBeat","statusChangeTime"]);
		
		$index=1;
		$orders=$this->orderQueueByStatus(AssetTradeStatus\Approved,true);

		foreach($orders as $s) {

				$quote=$this->market()->assetQuote($s->assetId());				
				$currentQuote=0;			
				if ($s->tradeOp()==AssetTradeOperation\Buy) $currentQuote=$quote->buyQuote();
				if ($s->tradeOp()==AssetTradeOperation\Sell) $currentQuote=$quote->sellQuote();

				if (!$s->done() && $s->status()==AssetTradeStatus\Approved) {
				$waitBeats=$this->market()->beat()-$s->statusChangeBeat();
				$quantity=0;
				$doable=$this->orderIsDoable($s,$quantity,false);
				$ds->addRow([
					$index,
					$s->traderId(),
					$s->queueId(),
					$s->assetId(),
					AssetTradeOperation\toCanonical($s->tradeOp()),
					$this->market()->textFormater()->formatQuantity($s->quantity()),
					$this->market()->textformater()->formatDecimal($s->targetQuote(),$quote->toAssetId(),""),
					$this->market()->textFormater()->formatDecimal($currentQuote,$quote->toAssetId(),""),
					$this->market()->textFormater()->formatInt($waitBeats),
					AssetTradeStatus\toCanonical($s->status()),
					$doable ? "true":"false",
					$s->statusChangeBeat(),
					$this->textFormatter->dateLegend($s->statusChangeTime())
				]);				
				++$index;
			}
		} 

		return $ds->toCsvRet();
	}

	function assetBuyPendingQuantity($assetId) {		
			$quantity=0;					
			foreach($this->orderQueue() as $s) {
				if ($s->assetId()!=$assetId) continue;
				if ($s->tradeOp()!=AssetTradeOperation\Buy) continue;
				if ($s->done() || 
					($s->status()!=AssetTradeStatus\Approved && 
					$s->status()!=AssetTradeStatus\Suggested )) continue;
				
				$quantity+=$s->quantity();									
			}
		

			return $quantity;
		}

	function queueCancelledAsCsv() {
		$ds=new DataSet\DataSet(["index","traderId","queueId","assetId","tradeOp","quantity","quote","status","doable","statusChangeBeat","statusChangeTime"]);
		$index=1;
		$orders=$this->orderQueueByStatus(AssetTradeStatus\Cancelled,false);
		foreach($orders as $s) {
				if ($s->status()==AssetTradeStatus\Cancelled) {
				$quote=$this->market()->assetQuote($s->assetId());				
				$quantity=0;
				$doable=$this->orderIsDoable($s,$quantity,false);
				$ds->addRow([
					$index,
					$s->traderId(),
					$s->queueId(),
					$s->assetId(),
					AssetTradeOperation\toCanonical($s->tradeOp()),
					$this->market()->textFormater()->formatQuantity($s->quantity()),
					$this->market()->textFormater()->formatDecimal($s->targetQuote(),$quote->toAssetId(),"",$this->market()->quoteDecimals()),
					AssetTradeStatus\toCanonical($s->status()),
					$doable ? "true":"false",
					$s->statusChangeBeat(),
					Nano\nanoTextFormatter()->dateLegend($s->statusChangeTime())
				]);			
				++$index;	
			}
		} 

		return $ds->toCsvRet();
	}

	function monthRoi(&$orderBuy,&$orderSell) {
		$delta=$orderSell->doneTime()-$orderBuy->doneTime();		
		$gain=($orderSell->doneQuote()-$orderBuy->doneQuote())*$orderBuy->quantity();
		$roi=100*$gain/($orderBuy->doneQuote()*$orderBuy->quantity());
		$monthRoi=$delta>0 ? $roi*30*24*60*60/$delta : 1000*1000;
		return $monthRoi;
	}

	function queueDoneAsCsv() {		
		$ds=new DataSet\DataSet(["index","traderId","queueId","assetId","tradeOp","quantity","limitQuote","doneQuote","doneBalance","roi","monthRoi","doneWait","valuation","status","doable","statusChangeBeat","statusChangeTime","doneBeat","doneTime"]);
		$orders=$this->orderQueueByStatus(AssetTradeStatus\Approved,false);
		$quantitySum=0;

		$index=1;
		foreach($orders as $s) {				
				$quote=$this->market()->assetQuote($s->assetId());				
				//if ($s->assetId()!="ADA-USD") continue;

				if ($s->done()) {					
					$quantity=0;
					$doable=$this->orderIsDoable($s,$quantity,false);
					$doneBalanceStr="";
					$doneRoiMonthStr="";
					$doneRoiStr="";
					$doneWaitStr=""; //$this->market()->textFormater()->formatInt($this->market()->beat()-$s->doneBeat()); // en caso de no estar hecha la venta (más abajo se hace la diferencia contra la venta)
					$valuationStr="";

					if ($s->parentQueueId()!=null) {
						$parentOrder=$this->findOrderById($s->parentQueueId());
						if ($parentOrder->done() && $s->done()) {
							$valuation=$s->quantity()*$s->doneQuote();
							$valuationStr=$this->market()->textFormater()->formatDecimal($valuation,$quote->toAssetId());
							$parentQuote=$parentOrder->doneQuote();
							$doneBalance=($s->doneQuote()-$parentQuote)*$s->quantity();	
							$doneRoi=$doneBalance*100/$valuation;
							$doneRoiStr=$this->market()->textFormater()->formatPercent($doneRoi);
							$doneBalanceStr=$this->market()->textFormater()->formatDecimal($doneBalance,$quote->toAssetId());
							$doneRoiMonthStr=$this->market()->textFormater()->formatPercent($this->monthRoi($parentOrder,$s));
							$doneWaitStr=$this->market()->textFormater()->formatInt($s->doneBeat()-$parentOrder->doneBeat());
						}
					}
				

				if ($s->tradeOp()==AssetTradeOperation\Buy) $quantitySum+=$s->quantity();
				if ($s->tradeOp()==AssetTradeOperation\Sell) $quantitySum-=$s->quantity();
				
				$ds->addRow([
					$index,
					$s->traderId(),
					$s->queueId(),
					$s->assetId(),
					AssetTradeOperation\toCanonical($s->tradeOp()),
					$this->market()->textFormater()->formatQuantity($s->quantity()),
					$this->market()->textFormater()->formatDecimal($s->targetQuote(),$quote->toAssetId()),
					$this->market()->textFormater()->formatDecimal($s->doneQuote(),$quote->toAssetId()),
					$doneBalanceStr,
					$doneRoiStr,
					$doneRoiMonthStr,
					$doneWaitStr,
					$valuationStr,
					AssetTradeStatus\toCanonical($s->status()),
					$doable ? "true":"false",
					$s->statusChangeBeat(),
					Nano\nanoTextFormatter()->dateLegend($s->statusChangeTime()),
					$s->doneBeat(),
					Nano\nanoTextFormatter()->dateLegend($s->doneTime())
				]);			
				++$index;	
			}

		} 

		return $ds->toCsvRet();
	}

	function portfolio() {
		return $this->portfolio;
	}

	function status(&$market) {
		Nano\msg(sprintf("Trader:%s %s portfolioValuation:%s",$this->traderId(),$this->portfolio(),Nano\nanoTextFormatter()->moneyLegend($market->portfolioValuation($this->portfolio()) ) ));
		// Nano\msg(sprintf("Trader:%s queue:%s",$this->traderId(),$this->orderQueueToCanonical() ));
	
	}

	function formatDecimal($value,$assetId="") {		
		$assetIdStr=sprintf('<span class="assetId">%s</span>',$assetId);
		$value=number_format($value,4).($assetId!="" ? " $assetIdStr" : "");
		return sprintf('<span class="quote">%s</span>',$value);
	}

	function formatQuantity($value) {
		return sprintf('<span class="quantity">%s</span>',number_format($value,4));
	}

	function dailyWaitFromMarketOpen($beats=null) {
		if ($beats!=null) $this->dailyWaitFromMarketOpen=$beats;
			return $this->titdailyWaitFromMarketOpen;		
	}

	function dailyWaitFromMarketClose($beats=null) {
		if ($beats!=null) $this->dailyWaitFromMarketClose=$beats;
			return $this->dailyWaitFromMarketClose;
	}
	
	function openTabToBroker($openTabToBroker=null) {
		if ($openTabToBroker!=null) $this->openTabToBroker=$beats;
			return $this->openTabToBroker;
	}

	function botArenaId() {
		if ($this->botArenaId==null) Nano\nanoCheck()->checkFailed("botArenaId should be assigned");
		return $this->botArenaId;
	}

	function orderQueue() {		
		$os=[];		
		$query=sprintf("SELECT * FROM assetTradeOrder as o						
							WHERE botArenaId='%s' 
								AND traderId='%s'",$this->botArenaId(),$this->traderId());

		$r=$this->pdo()->query($query);

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

	function save() {
		$this->portfolio->save();
		$this->queueSave();
	}

	function queueSave() {
		Nano\nanoCheck()->checkDiskAvailable();

		if (!file_exists("content/MarketTrader")) mkdir("content/MarketTrader");
		file_put_contents(sprintf("content/MarketTrader/marketTrader.%s.%s.csv",
			$this->market()->marketId(),$this->traderId()),$this->queueCanonicalAsCsv() );
	}

	function traderRecover() {
		$this->portfolio->portfolioRecover();
		$this->queueRecover();
	}

	function queueRecover() {
		//debug_print_backtrace();

		$fileName=sprintf("content/MarketTrader/marketTrader.%s.%s.csv",$this->market()->marketId(),$this->traderId());
		if (file_exists($fileName)) {
			$csv=file_get_contents($fileName );
			$rows=Nano\nanoCsv()->csvContentToArray($csv,';');
			$this->orderQueue()->reset();

			foreach($rows as $row) {
				$q=new AssetTradeOrder\AssetTradeOrder();

				foreach($row as $key=>$value) {
					$q->$key=$value;
				}
				$this->orderQueue()->insert($q);
			}		

			Nano\msg(sprintf("MarketTrader: traderId:%s.%s queueRecover: done",$this->market()->marketId(),$this->traderId() ));			
		} else {
			Nano\msg(sprintf("MarketTrader: traderId:%s.%s queueRecover: ignored msg: no file from which recover",$this->market()->marketId(),$this->traderId() ));
		}
	}

	function queueCanonicalAsCsv() {
		$ds=new DataSet\DataSet(explode(",","traderId,queueId,assetId,tradeOp,quantity,targetQuote,status,done,statusChangeBeat,statusChangeTime,queueBeat,parentQueueId,notified,doneBeat,doneTime,doneQuote"));
					
		$orders=$this->orderQueue()->values();
		foreach($orders as $q) {
			$ds->addRow([
				$q->traderId(),
				$q->queueId(),
				$q->assetId(),
				$q->tradeOp(),
				$q->quantity(),
				$q->targetQuote(),
				$q->status(),
				$q->done(),
				$q->statusChangeBeat(),
				$q->statusChangeTime(),
				$q->queueBeat(),
				$q->parentQueueId(),
				$q->notified(),
				$q->doneBeat(),
				$q->doneTime(),
				$q->doneQuote()
			]);
		}

		return $ds->toCsvRet();		
	}

	function telegramBot() {
		return "5138012656:AAG6mFxtlvZsGRNUnSm0sCcqd8Hkei1KFgg";
	}

	function telegram($msg) {
	        $telegrambot=$this->telegramBot();
	        $telegramchatid=$this->telegramChatId();
	        $url='https://api.telegram.org/bot'.$telegrambot.'/sendMessage';$data=array('chat_id'=>$telegramchatid,'text'=>$msg,'parse_mode'=>"MarkDown"); //HTML
	        $options=array('http'=>array('method'=>'POST','header'=>"Content-Type:application/x-www-form-urlencoded\r\n",'content'=>http_build_query($data),),);
	        $context=stream_context_create($options);
	        $result=file_get_contents($url,false,$context);
	        return $result;
	}
}

?>