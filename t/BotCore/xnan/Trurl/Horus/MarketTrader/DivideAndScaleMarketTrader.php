<?php
namespace xnan\Trurl\Horus\MarketTrader;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Horus\AssetTradeStatus;
use xnan\Trurl\Horus\CryptoCurrency;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Horus\WorldSettings;
use xnan\Trurl\Horus\Asset;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End


AssetTradeOperation\Functions::Load;
Asset\Functions::Load;

error_reporting(E_ALL);

class DivideAndScaleMarketTrader extends MarketTrader {	
	var $stableAssetIds=array();
	var $statsBuyStory=array();
	var $statsSellStory=array();
	private $traderStats;

	function __construct($botArenaId,$traderId,$portfolioId) {		
		parent::__construct($botArenaId,$traderId,$portfolioId);		
	}

	function setupSettings() {
		parent::setupSettings();
		$s=BotWorld\BotWorld::instance()->settings();		

		$s->registerChangeListener("dsMarketTrader.waitBeats",$this);
		$s->registerChangeListener("dsMarketTrader.maxAssetPercentage",$this);
		$s->registerChangeListener("dsMarketTrader.waitBeatsForAssetRepeat",$this);
		$s->registerChangeListener("dsMarketTrader.marketStats.maxHistoryBeats",$this);
		$s->registerChangeListener("dsMarketTrader.buyCicleCut",$this);
		$s->registerChangeListener("dsMarketTrader.sellCicleCut",$this);
		$s->registerChangeListener("dsMarketTrader.maxBuySuggestions",$this);
		$s->registerChangeListener("dsMarketTrader.buyLimitFactor",$this);
	}


	function waitBeats($waitBeats=null) {
		return Horus\persistence()->dsTraderWaitBeats($this->botArenaId(),$this->traderId(),$waitBeats);	
	}

	function phase($phase=null) {
		return Horus\persistence()->dsTraderPhase($this->botArenaId(),$this->traderId(),$phase);	
	}

	function startBeat($startBeat=null) {
		return Horus\persistence()->dsTraderStartBeat($this->botArenaId(),$this->traderId(),$startBeat);	
	}


	function maxAssetPercentage($maxAssetPercentage=null) {
		return Horus\persistence()->dsTraderMaxAssetPercentage($this->botArenaId(),$this->traderId(),$maxAssetPercentage);	
	}

	function waitBeatsForAssetRepeat($waitBeatsForAssetRepeat=null) {
		return Horus\persistence()->dsTraderWaitBeatsForAssetRepeat($this->botArenaId(),$this->traderId(),$waitBeatsForAssetRepeat);	
	}

	function assetShortTrendLowCut($assetShortTrendLowCut=null) {
		return Horus\persistence()->dsTraderAssetShortTrendLowCut($this->botArenaId(),$this->traderId(),$assetShortTrendLowCut);	
	}

	function assetMediumTrendLowCut($assetMediumTrendLowCut=null) {
		return Horus\persistence()->dsTraderAssetMediumTrendLowCut($this->botArenaId(),$this->traderId(),$assetMediumTrendLowCut);	
	}

	function marketShortTrendLowCut($marketShortTrendLowCut=null) {
		return Horus\persistence()->dsTraderMarketShortTrendLowCut($this->botArenaId(),$this->traderId(),$marketShortTrendLowCut);	
	}

	function marketMediumTrendLowCut($marketMediumTrendLowCut=null) {
		return Horus\persistence()->dsTraderMarketMediumTrendLowCut($this->botArenaId(),$this->traderId(),$dsTraderMarketMediumTrendLowCut);	
	}

	function buyCicleCut($buyCicleCut=null) {
		return Horus\persistence()->dsTraderBuyCicleCut($this->botArenaId(),$this->traderId(),$buyCicleCut);	
	}


	function sellCicleCut($sellCicleCut=null) {
		return Horus\persistence()->dsTraderSellCicleCut($this->botArenaId(),$this->traderId(),$sellCicleCut);	
	}


	function maxBuySuggestions($maxBuySuggestions=null) {
		return Horus\persistence()->dsTraderMaxBuySuggestions($this->botArenaId(),$this->traderId(),$maxBuySuggestions);	
	}


	function buyLimitFactor($buyLimitFactor=null) {
		return Horus\persistence()->dsTraderBuyLimitFactor($this->botArenaId(),$this->traderId(),$buyLimitFactor);	
	}



	function onSettingsChange($key,$params) {
		exit("onSettingsChange: unsupported/deferred");
		parent::onSettingsChange($key,$params);
		if ($params["traderId"]==$this->traderId()) {
			if ($key=="dsMarketTrader.waitBeats") { $this->waitBeats=$params["settingsValue"]; }			
			if ($key=="dsMarketTrader.maxAssetPercentage") { $this->maxAssetPercentage=$params["settingsValue"]; }	
			if ($key=="dsMarketTrader.waitBeatsForAssetRepeat") { $this->waitBeatsForAssetRepeat=$params["settingsValue"]; }
			if ($key=="dsMarketTrader.marketStats.maxHistoryBeats") { $this->market()->marketStats()->get()->setupMaxHistoryBeats($params["settingsValue"]); }
			if ($key=="dsMarketTrader.buyCicleCut") { $this->buyCicleCut=$params["settingsValue"]; }
			if ($key=="dsMarketTrader.sellCicleCut") { $this->sellCicleCut=$params["settingsValue"]; }
			if ($key=="dsMarketTrader.maxBuySuggestions") { $this->sellCicleCut=$params["settingsValue"]; }
			if ($key=="dsMarketTrader.buyLimitFactor") { $this->buyLimitFactor=$params["settingsValue"]; }
		}
	}


	function traderStats() {
		return $this->traderStats;
	}

	function setupMarket(&$market) {
		parent::setupMarket($market);
		$marketStats=$this->market()->marketStats();
		$this->traderStats=new MarketStats\DsTraderStats($marketStats,$this);
	}

	function addTraderCustomSettings($ds) {
		$traderId=$this->traderId();
		$ds->addRow(["boundMarketId","ID de mercado asociado",$this->market()->marketId() ]);
		$ds->addRow(["boundMarketClazz","Clase de mercado asociado",get_class($this->market())]);
		$ds->addRow(["boundMarketBeat","Pulso de mercado asociado",$this->market()->beat()]);
		$ds->addRow(["waitBeats","Pulsos de espera antes de operar",$this->textFormatter()->formatInt($this->waitBeats(),"settingsKey=dsMarketTrader.waitBeats&traderId=$traderId")]);		
		$ds->addRow(["phase","Fase de operación",$this->phase()]);
		$ds->addRow(["maxAssetPercentage","Porcentaje máximo en portfolio de un activo",$this->textFormatter()->formatDecimal($this->maxAssetPercentage(),"","settingsKey=dsMarketTrader.maxAssetPercentage&traderId=$traderId")]);
		$ds->addRow(["waitBeatsForAssetRepeat","Pulsos de espera antes de sugerir de nuevo un activo",$this->textFormatter()->formatInt($this->waitBeatsForAssetRepeat(),"settingsKey=dsMarketTrader.waitBeatsForAssetRepeat&traderId=$traderId")]);		
		$ds->addRow(["startBeat","Pulso de comienzo de operaciones",$this->startBeat()]);			
		$ds->addRow(["marketStats.maxHistoryBeats","Tamaño de la ventana de estadísticas en pulsos",$this->textFormatter()->formatInt($this->market()->marketStats()->maxHistoryBeats(),"settingsKey=dsMarketTrader.marketStats.maxHistoryBeats&traderId=$traderId")]);
		$ds->addRow(["buyCicleCut","Umbral de corte de ciclo para compras",$this->textFormatter()->formatDecimal($this->buyCicleCut(),"","settingsKey=dsMarketTrader.buyCicleCut&traderId=$traderId")]);
		$ds->addRow(["sellCicleCut","Umbral de corte de ciclo para ventas",$this->textFormatter()->formatDecimal($this->sellCicleCut(),"","settingsKey=dsMarketTrader.sellCicleCut&traderId=$traderId")]);		
		$ds->addRow(["maxBuySuggestions","Máximo de sugerencias de compra simultáneas",$this->textFormatter()->formatInt($this->maxBuySuggestions(),"settingsKey=dsMarketTrader.maxBuySuggestions&traderId=$traderId")]);
		$ds->addRow(["buyLimitFactor","Factor de ajuste para compra límite",$this->textFormatter()->formatDecimal($this->buyLimitFactor(),"","settingsKey=dsMarketTrader.buyLimitFactor&traderId=$traderId")]);

	}

	function traderReset() {
		exit("traderReset: deferred/unsupported");
		parent::traderReset();
		$this->phase(1);
		$this->startBeat(-1);
		//$this->market()->marketStats()->get()->marketStatsReset();
	}

	function traderHistoryLabels($xcount=100) {
		if (count($this->statsValueHistory)==0) return $this->arrayAsJsonList([],$xcount); 
		$firstAssetId=array_keys($this->statsValueHistory)[0];
		return $this->arrayAsJsonList(array_keys($this->statsValueHistory[$firstAssetId]),$xcount);
	}
	
	function traderBoyDatasetsJson($xcount=100) {
		$json="";
		foreach($this->statsBuyStory as $assetId=>$buyHistory) {
			if ($assetId=="USD") continue;
			if ($assetId!="LUNA1-USD") continue;
			if (strlen($json)>0) $json.=",";
			//$json.=$this->market()->marketStats()->get()->marketHistoryDatasetJson($assetId,market,$xcount);
			$json.=$this->market()->marketStats()->get()->marketHistoryDatasetJson($assetId,$buyHistory,$xcount,"$assetId-buy",true,"rgb(0,150,0)");
		}
		foreach($this->statsSellStory as $assetId=>$sellHistory) {
			if ($assetId=="USD") continue;
			if ($assetId!="LUNA1-USD") continue;
			if (strlen($json)>0) $json.=",";
			//$json.=$this->market()->marketStats()->get()->marketHistoryDatasetJson($assetId,market,$xcount);
			$json.=$this->market()->marketStats()->get()->marketHistoryDatasetJson($assetId,$sellHistory,$xcount,"$assetId-sell",true,"rgb(150,0,0)");
		}

		foreach($this->market()->marketStats()->get()->statsValueHistory as $assetId=>$valueHistory) {
			if ($assetId=="USD") continue;
			if ($assetId!="LUNA1-USD") continue;
			if (strlen($json)>0) $json.=",";
			//$json.=$this->market()->marketStats()->get()->marketHistoryDatasetJson($assetId,market,$xcount);
			$json.=$this->market()->marketStats()->get()->marketHistoryDatasetJson($assetId,$valueHistory,$xcount,"$assetId");
		}

		return sprintf("[%s]",$json);
	}

	function traderHistoryAsJson($xcount=100) {		
		 return sprintf("{ labels: %s, datasets: %s }",$this->market()->marketStats()->get()->marketHistoryLabels($xcount)
		 	, $this->traderBoyDatasetsJson($xcount));
	}
	
	function markStable($assetId) {
		$this->stableAssetIds[]=$assetId;
	}

	function assetSuggestionAllowed($assetId) {
		foreach($this->orderQueue() as &$order) {
			if ($order->assetId()==$assetId && !$order->done() && ($order->status()==AssetTradeStatus\Cancelled || $order->status()==AssetTradeStatus\Rejected) ) {
				if (($order->statusChangeBeat()+$this->waitBeatsForAssetRepeat())>$this->market()->beat() ) {
					return false;
				}
			}
		}
		return true;
	}

	//phase 1: buy stables
	//phase 2: trade unstables on lows
	function trade(&$market) {
			Nano\nanoPerformance()->track("dsTrader.trade");
			if ($this->phase()==1) $this->tradePhase1($market);
			if ($this->phase()==2) $this->tradePhase2($market);
			Nano\nanoPerformance()->track("dsTrader.trade");
//			BotWorld\BotWorld::instance()->summaryWrite();
	}


	function statsFull() {
		return 
			$this->marketStats()->isFull() && 
			$this->marketStatsMedium()->isFull() &&
			$this->marketStatsLong()->isFull();
	}


	function wakeupWaitDone() {
		return ($this->market()->beat()-$this->startBeat())>=$this->waitBeats();
	}

	function tradePhase1(&$market) {
		if ($this->startBeat()==-1) $this->startBeat($market->beat());
		//printf("(%s-%s)>=%s) ? %s \n",$market->get()->beat(),$this->startBeat,$this->waitBeats,
		//(($market->get()->beat()-$this->startBeat)>=$this->waitBeats) ? "true" : "false");
	

		if ($this->wakeupWaitDone() && $this->statsFull() ) {			
			$this->nextPhase();
		} else {

			Nano\msg(sprintf("dsTrader: %s tradePhase1: wakeUpWaitDone:%s shortFill:%s mediumFill:%s longFill:%s",$this->traderId() , 
				$this->textFormatter()->formatBool($this->wakeupWaitDone()), 
				$this->textFormatter()->formatDecimal($this->marketStats()->usage()),
				$this->textFormatter()->formatDecimal($this->marketStatsMedium()->usage()),
				$this->textFormatter()->formatDecimal($this->marketStatsLong()->usage())				
					 ));

		}
	}


	function cicleToValue($cicle,$min,$max) {

		$cicleValue=$max!=$min ? (($cicle+1)/2*($max-$min))+$min : $max;

		//print_r(["cicleToValue","cicle"=>$cicle,"min"=>$min,"max"=>$max,"cicleValue"=>$cicleValue]);
		
		return $cicleValue;
	}

	function marketStats(){
		$s=$this->market()->marketStats();
		return $s;
	}

	function marketStatsMedium(){
		$s=$this->market()->marketStatsMedium();
		return $s;
	}

	function marketStatsLong(){
		$s=$this->market()->marketStatsLong();
		return $s;
	}


	function statsMediumAsCsv() {
		return $this->customStatsAsCsv($this->marketStatsMedium());
	}

	function statsLongAsCsv() {
		return $this->customStatsAsCsv($this->marketStatsLong());
	}

	function statsAsCsv() {		
		return $this->customStatsAsCsv($this->marketStats());
	}

	function customStatsAsCsv(&$marketStats) {		
		$ds=new DataSet\DataSet(["synchedBeat","beat","beatMultiplier","assetId","value","mean","max","min","linearSlope","maxBuyByStrategy","cicle","eligibleByCycle","maxBuyQuantityByStrategy","eligibleByQuantity","earn","eligibleByEarn"]);
		
		foreach($this->marketStats()->assetIds() as $assetId) {			
			$statsValue=$marketStats->statsScalar(MarketStats\MarketStats::SValue,$assetId);
			$statsMean=$marketStats->statsScalar(MarketStats\MarketStats::SMean,$assetId);
			$statsMax=$marketStats->statsScalar(MarketStats\MarketStats::SMax,$assetId);
			$statsMin=$marketStats->statsScalar(MarketStats\MarketStats::SMin,$assetId);
			$statsCicle=$marketStats->statsScalar(MarketStats\MarketStats::SCicle,$assetId);	

			$statsLinearSlope=$marketStats->statsScalar(MarketStats\MarketStats::SLinearSlope,$assetId);			
			
			$statsEligibleByCycle=$this->traderStats()->statsScalar(MarketStats\DsTraderStats::SEligibleByCycle,$assetId);

			$statsEligibleByQuantity=$this->traderStats()->statsScalar(MarketStats\DsTraderStats::SEligibleByQuantity,$assetId);

			$statsEligibleByEarn=$this->traderStats()->statsScalar(MarketStats\DsTraderStats::SEligibleByEarn,$assetId);

			$statsEarn=$this->traderStats()->statsScalar(MarketStats\DsTraderStats::SEarn,$assetId);

			$this->market()->textFormater()->textFormat("text"); //TODO remover, solo testing.
			
			$ds->addRow([
				"synchedBeat"=>$marketStats->synchedBeat(),				
				"beat"=>$this->market()->beat(),
				"beatMultiplier"=>$marketStats->beatMultiplier(),
				"assetId"=>$assetId,
				"value"=>$this->market()->textFormater()->formatDecimal($statsValue),
				"mean"=>$this->market()->textFormater()->formatDecimal($statsMean),
				"max"=>$this->market()->textFormater()->formatDecimal($statsMax),
				"min"=>$this->market()->textFormater()->formatDecimal($statsMin),
				"linearSlope"=>$this->market()->textFormater()->formatDecimal($statsLinearSlope),
				"maxBuyByStrategy"=>$this->market()->textFormater()->formatQuantity(
					$this->maxBuyByStrategy($this->market(),$assetId),$this->market()->defaultExchangeAssetId() ),
				"cicle"=>$this->market()->textFormater()->formatDecimal($statsCicle),
				"eligibleByCycle"=>$this->market()->textFormater()->formatBool($statsEligibleByCycle),
				"maxBuyQuantityByStrategy"=>$this->market()->textFormater()->formatDecimal(
					$this->maxBuyQuantityByStrategy($this->market(),$assetId) ),
				"eligibleByQuantity"=>$this->market()->textFormater()->formatBool($statsEligibleByQuantity),
				"statsEarn"=>$this->market()->textFormater()->formatDecimal($statsEarn),
				"statsEligibleByEarn"=>$this->market()->textFormater()->formatBool($statsEligibleByEarn)

			]
			);			


		}

		return $ds->toCsvRet();	
	}




	function maxBuyQuantityByStrategy(&$market,$assetId) {
		$buyQuote=$market->assetQuote($assetId)->buyQuote(); // precio de compra ($)		"
		return $buyQuote!=0 ? $this->maxBuyByStrategy($market,$assetId)/$buyQuote : 0;
	}

	function pickAssetIdByCicle(&$market,$nextCicle) {
		$minAssetId=-1;
		$minCicle=1000*1000;
		$maxCicle=-1000*1000;
		$maxAssetId=-1;		
		
		foreach($this->market()->assetIds() as $assetId) {
			$value=$this->marketStats()->statsScalar(MarketStats\MarketStats::SValue,$assetId);

			Asset\checkAssetId($assetId);
			// if (!$this->assetSuggestionAllowed($assetId)) echo "**NOT ALLOWED:$assetId<br>";
			if ($assetId==$this->market()->defaultExchangeAssetId()) continue;
			if (!$this->assetSuggestionAllowed($assetId)) continue;

			$mean=$this->marketStats()->statsScalar(MarketStats\MarketStats::SMean,$assetId);
			$max=$this->marketStats()->statsScalar(MarketStats\MarketStats::SMax,$assetId);
			$min=$this->marketStats()->statsScalar(MarketStats\MarketStats::SMin,$assetId);
			$cicle=$this->marketStats()->statsScalar(MarketStats\MarketStats::SCicle,$assetId);// entre -1 y +1 respectivamente

			//print "pick assetId: $assetId cicle:$cicle minCicle:$minCicle<br>";
			if ($minAssetId==-1 || $minCicle>=$cicle) {
				$minAssetId=$assetId;
				$minCicle=$cicle;				
			}
			if ($maxAssetId==-1 || $maxCicle<=$cicle) {
				$maxAssetId=$assetId;
				$maxCicle=$cicle;				
			}
		}
		
		$pick=[$minAssetId,
				$minCicle,
				$maxAssetId,
				$maxCicle,
				$this->cicleToValue($nextCicle,
					$this->marketStats()->statsScalar(MarketStats\MarketStats::SMin,$minAssetId),
					$this->marketStats()->statsScalar(MarketStats\MarketStats::SMax,$minAssetId)
				)];


		//print_r(["pick",$pick]);
		return $pick;
	}

	function maxBuyByStrategy(&$market,$assetId) {
		$valuation=$market->portfolioValuation($this->portfolio() ); // valuacion de porfolio ($)
		
		$buyQuote=$market->assetQuote($assetId)->buyQuote(); // precio de compra ($)

		//print "ASSET-QUANTITY:".$this->portfolio()->assetQuantity($assetId)."\n";
		$assetValuation=$this->portfolio()->assetQuantity($assetId)*$buyQuote; // valuacion de tenencias del activo ($)

		$assetValuationPending=$this->assetBuyPendingQuantity($assetId)*$buyQuote; // valuacion de tenencias del activo pendientes ($)

		// print "$assetId: assetValuationPending: $assetValuationPending\n";

		$maxBuy=$buyQuote>0 ? max(($valuation*$this->maxAssetPercentage() )-$assetValuation-$assetValuationPending,0) : 0; // maxima compra segun porcentaje permitido considerando lo que ya tiene ($)
		
		//print "maxBuy;$maxBuy (($valuation*".$this->maxAssetPercentage().")-$assetValuation)/$buyQuote)\n";

		$maxAvailable=$market->maxBuyQuantity($this->portfolio(),$assetId); // maxima cantidad (#) comprable segun dinero disponible en cartera, precio de mercado del activo y costo de comisiones

		$maxBuyAvailable=$maxAvailable*$buyQuote;

		$maxBuyLimitedUnits=floor($maxBuy/$this->settingBuyUnits())*$this->settingBuyUnits(); // maximo ($) segun porcentaje permitido, redondeado para abajo segun cantidad de unidades minimas


		$buy=max(min($maxBuyLimitedUnits,$maxBuyAvailable),0); // maximo comprable ($) combinando maximo porcentaje de cartera y disponible para compra descontando comisiones
		if ($buy<$this->settingBuyMinimum()) $buy=0; // maximo comprable ($) considerando el lìmite de compra mìnima (en caso de no llegar al mínimo da cero)
		//print "************** buy $assetId: $buy<BR>";

		//print_r(["maxBuyByStrategy","assetId"=>$assetId,"buyQuote"=>$buyQuote,"valuation(of portfolio)(USD)"=>$valuation,"assetValuation(asset in portfolio)(USD)"=>$assetValuation,"assetValuationPending)(asset pending to buy)(USD)"=>$assetValuationPending,
		//"maxAvailable(asset #)"=>$maxAvailable,"maxBuyAvailable(asset USD)"=>$maxBuyAvailable,"maxBuy(asset allowed by free % USD)"=>$maxBuy,"maxBuyLimitedUnits"=>$maxBuyLimitedUnits,"buy"=>$buy]);
		
		return $buy;
	}


	function doBuySell(&$market,$assetId) {
		
		$buyQuote=$this->buyAtLimit($market,$assetId,$this->buyLimitFactor() ); //1.01
		$sellQuote=$this->traderStats()->assetSellQuote($assetId);//*0.99;
		$quantity=$this->traderStats()->statsMaxBuyQuantity($assetId);

		$op=AssetTradeOperation\Buy;
		$negOp=AssetTradeOperation\Sell;

		$quote=$op==AssetTradeOperation\Buy ? $buyQuote : $sellQuote;

		Nano\msg(sprintf("### doBuySell: assetId: %s buyQuote: %s sellQuote(later): %s quantity:%s",
			$assetId,$buyQuote,$sellQuote,$quantity));

		$newQueueId=$this->queueOrUpdateOrder(null,$assetId,$op,$quantity,$quote);

		$this->queueOrUpdateOrder($newQueueId,$assetId,$negOp,$quantity,$sellQuote,$this->defaultStatus(),$newQueueId);
	}

	function allowedByMaxPending() {
		//print "FASE2 traderId: $this->traderId: ".($this->pendingBuySuggestionsCount())." maxBuySugg: ".($this->maxBuySuggestions() )."\n";
		return $this->pendingBuySuggestionsCount()<$this->maxBuySuggestions();
	}

	function tradePhase2(&$market) {		
		if (!$this->statsFull()) {

			Nano\msg(sprintf("dsTrader: msg: stats not full, back to phase 1."));			
			$this->previousPhase();

		} else if (!$this->allowedByMaxPending()) {

			Nano\msg(sprintf("dsTrader: %s tradePhase2: msg: too much pending orders",$this->traderId()));

		} else {
			Nano\msg(sprintf("dsTrader: %s tradePhase2: searching buy candidates",$this->traderId()));
		 
			$pickedIds=$this->traderStats()->statsEligiblesFinal();

			if (count($pickedIds)==0) {

				Nano\msg(sprintf("dsTrader: marketId:%s traderId:%s assetId:$assetId msg: no eligible asset found",$this->market()->marketId(),$this->traderId() ));
				 return;	

			}	else {

				$assetId=$pickedIds[0];
				$this->doBuySell($market,$assetId);

			}

		}

	}

	function previousPhase() {
		$this->phase($this->phase()-1);	
	}
	function nextPhase() {
		$this->phase($this->phase()+1);	
	}


	function egInfo($assetId) {
		$egMarketTrend1=$this->traderStats()->statsEligibleMarketByShortTrend();
		$egMarketTrend2=$this->traderStats()->statsEligibleMarketByMediumTrend();
		$egTrend1=$this->traderStats()->statsEligibleByShortTrend($assetId);
		$egTrend2=$this->traderStats()->statsEligibleByMediumTrend($assetId);
		$egCycle=$this->traderStats()->statsEligibleByCycle($assetId);
		$egEarn=$this->traderStats()->statsEligibleByEarn($assetId);
		$egQuantity=$this->traderStats()->statsEligibleByQuantity($assetId);
		$egFinal=$this->traderStats()->statsEligibleFinal($assetId);

		Nano\msg(sprintf("dsTrader: %s pick: assetId:%s egMarketTrend1:%s egMarketTrend2:%s egTrend1:%s egTrend2:%s egCycle:%s egQuantity:%s egEarn:%s egFinal:%s",
			$this->traderId(),
			$assetId,
			$this->market()->textFormater()->formatBool($egMarketTrend1),
			$this->market()->textFormater()->formatBool($egMarketTrend2),
			$this->market()->textFormater()->formatBool($egTrend1),
			$this->market()->textFormater()->formatBool($egTrend2),
			$this->market()->textFormater()->formatBool($egCycle),
			$this->market()->textFormater()->formatBool($egQuantity),
			$this->market()->textFormater()->formatBool($egEarn),
			$this->market()->textFormater()->formatBool($egFinal),
		));

	}

}



?>
