<?php
namespace xnan\Trurl\Horus\Market;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Nano\TextFormatter;
use xnan\Trurl\Horus\WorldSettings;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Horus\MarketSchedule;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

abstract class AbstractMarket implements Market {
	private $marketId;

	var $onBeat;	
	var $textFormater;
	var $marketSchedule=null;
	var $useHistory=false;
	var $marketStats,$marketStatsLong,$marketStatsMedium;
	// statsMediumBeatMultiplier:100  aprox. 50 muestras en una semana
	// statsLongBeatMultiplier:400 aprox. 50 muestras en un mes.

	abstract function assets();

	abstract function assetIds();

	abstract function assetIdsByType($asseType);

	abstract function tradeFixedFees();

	function pdo() {		
		return (BotWorld\BotWorld::instance())->pdo();
	}
	

	function __construct($marketId,$useHistory=false) {
		$this->textFormatter=Nano\newTextFormatter();
		$this->marketId=$marketId;
		$this->useHistory=$useHistory;
		$this->onBeat=new Observer\Observable();
		$this->setupMarket();
	}

	protected function setupMarket() {
		$this->textFormatter()->defaultDecimals($this->quoteDecimals());
		$this->setupMarketSchedule();
		$this->setupMarketStats();			
		$this->setupSettings();
	}

	function textFormatter() {
		return $this->textFormatter;
	}

	private function setupMarketSchedule() {

		$query=sprintf("SELECT * FROM marketSchedule as s
						INNER JOIN market as m 
							ON  s.marketScheduleId=m.marketScheduleId 
								AND m.marketId='%s'",$this->marketId);

		$r=$this->pdo()->query($query);

		if ($row=$r->fetch()) {
			$s=new MarketSchedule\MarketSchedule();
			$s->marketOpenHour($row["marketOpenHour"]);
			$s->marketCloseHour($row["marketCloseHour"]);
			$s->marketWeekDaysOpen($row["marketWeekDaysOpen"]);
			$s->marketHolidays($row["marketHolidays"]);
			$s->marketTimeZone($row["marketTimeZone"]);
			$s->marketIsAlwaysOpen($row["marketIsAlwaysOpen"]);
			$s->marketBeatSeconds($row["marketBeatSeconds"]);
			$s->title($row["title"]);

			$this->marketSchedule=$s;
		} else {
			Nano\nanoCheck()->checkFailed("schedule not found for market $this->marketId");
		}
	}

	function maxHistoryBeats() {
		return Horus\persistence()->marketMaxHistoryBeats($this->marketId());
	}

	function statsLongBeatMultiplier() {
		return Horus\persistence()->marketStatsLongBeatMultiplier($this->marketId());
	}

	function statsMediumBeatMultiplier() {
		return Horus\persistence()->marketStatsMediumBeatMultiplier($this->marketId());
	}


	private function setupMarketStats() {
		$openHours=$this->marketSchedule()->marketOpenHoursCount();
		$openFactor=$openHours/24;
		$finalBeatMult=floor($this->statsLongBeatMultiplier()*$openFactor);

		$this->marketStats=new MarketStats\MarketStats($this,"marketStatsShort");
		
		$this->marketStatsLong=new MarketStats\MarketStats($this,"marketStatsLong");

		$this->marketStatsMedium=new MarketStats\MarketStats($this,"marketStatsMedium");
	}	

	function settingsAsCsv() {
		$ds=new DataSet\DataSet(["settingsKey","settingsDescription","settingsValue"]);
		
		$marketId=$this->marketId();

		$pollContentAgeDate=new \DateTime();
		$pollContentAgeDate->setTimestamp(time()-$this->pollContentAgeSeconds());
		$pollContentAgeDateStr=date_format($pollContentAgeDate,'Y-m-d H:i:s');

		$ds->addRow(["marketId","ID Mercado",$marketId]);
		$ds->addRow(["marketClazz","Clase de Mercado",get_class($this)]);
		$ds->addRow(["marketTitle","Título de Mercado",$this->textFormatter()->formatString($this->marketTitle(),"settingsKey=market.marketTitle&marketId=$marketId")]);
		$ds->addRow(["beat","Pulso",$this->beat()]);
		$ds->addRow(["tradeFixedFees","Costos fijos por operación",implode(", ",$this->tradeFixedFees()) ]);
		//$ds->addRow(["assetCount","Activos permitidos",$this->textFormatter()->formatInt($this->assetCount,"settingsKey=market.assetCount&marketId=$marketId")]);
		$ds->addRow(["assetIds","Activos",implode(", ",$this->assetIds() )]);
		$ds->addRow(["useHistory","Usa información histórica",$this->useHistory() ? "true" : "false"]);
		$ds->addRow(["defaultExchangeAssetId","Divisa de intercambio por defecto",$this->defaultExchangeAssetId()]);
		$ds->addRow(["quoteDecimals","Decimales de precisión para cotizaciones",$this->textFormatter()->formatInt($this->quoteDecimals(),"settingsKey=market.quoteDecimals&marketId=$marketId")]);		
		$ds->addRow(["pollContentAgeSeconds","Edad del contenido de la fuente (segundos)",$this->pollContentAgeSeconds() ]);		
		$ds->addRow(["pollContentAgeDate","Edad del contenido de la fuente (fecha)",$pollContentAgeDateStr ]);	
		$ds->addRow(["pollContentMaxAgeSeconds","Edad máxima de contenido de la fuente",$this->textFormatter()->formatInt($this->pollContentMaxAgeSeconds(),"settingsKey=market.pollContentMaxAgeSeconds&marketId=$marketId")]);
		$ds->addRow(["pollContentOutdated","Si el contenido de la fuente está desactualizado",$this->textFormatter()->formatBool($this->pollContentOutdated())]);

		$this->addCustomSettings($ds);
		return $ds->toCsvRet();
	}

	function addCustomSettings($ds) {

	}

	function setupSettings() {
		$s=BotWorld\BotWorld::instance()->settings();
		$s->registerChangeListener("market.marketTitle",$this);	
		$s->registerChangeListener("market.assetCount",$this);	
		$s->registerChangeListener("market.quoteDecimals",$this);	
		$s->registerChangeListener("market.pollContentMaxAgeSeconds",$this);	
		$s->registerChangeListener("market.reset",$this);	
	}

	function onSettingsChange($key,$params) {			
		if ($params["marketId"]==$this->marketId()) {
			if ($key=="market.marketTitle") { $this->marketTitle=$params["settingsValue"]; }
			if ($key=="market.assetCount") { $this->assetCount=$params["settingsValue"]; $this->setupAssets(); }
			if ($key=="market.quoteDecimals") { $this->quoteDecimals=$params["settingsValue"]; $this->setupAssets(); }

			if ($key=="market.pollContentMaxAgeSeconds") { $this->pollContentMaxAgeSeconds=$params["pollContentMaxAgeSeconds"]; }
			if ($key=="market.reset" && $params["settingsValue"]=="true") { $this->marketReset(); }
		}
	}

	function marketReset() {
		exit("marketReset: unsupported / deferred");
		$this->beat=0;
		$this->marketStats->get()->marketStatsReset();
	}

	function defaultExchangeAssetId($assetId=null) {
 		return Horus\persistence()->marketDefaultExchangeAssetId($this->marketId(),$assetId);	
	}

	function pollContentMaxAgeSeconds() {
 		return Horus\persistence()->marketPollContentMaxAgeSeconds($this->marketId());
	}		

	function useHistory() {
		return $this->useHistory;
	}
	function textFormater() {
		return $this->textFormatter;
	}
	
	function marketSchedule() {
		return $this->marketSchedule;
	}
	
	function onBeat() {
		return $this->onBeat;
	}

	function assetById($assetId) {
		Asset\checkAssetId($assetId);
		$defaultExchangeAssetId=$this->defaultExchangeAssetId();

		if  ($assetId==$defaultExchangeAssetId)
			 return new Asset\Asset($assetId,AssetType\Currency);		

		foreach($this->assets() as $asset) {
			if ($asset->assetId()==$assetId) return $asset;
		}
		throw new \exception("assetById: assetId:'$assetId' default: '$defaultExchangeAssetId' msg: asset not found");
	}

 	function tradeFixedFeesSum() {
 		$sum=0;
 		foreach($this->tradeFixedFees() as $fee) {
 			$sum+=$fee;
 		}
 		return $sum;
 	}


 	function botArenaId() {
 		return Horus\persistence()->marketBotArenaId($this->marketId());	
 	}

 	function marketId() {
 		return $this->marketId;
 	}

 	function quoteDecimals($quoteDecimals=null) {
 		if ($quoteDecimals!=null) {
			 $this->textFormatter()->defaultDecimals($quoteDecimals);
 		}
 		return Horus\persistence()->marketQuoteDecimals($this->marketId(),$quoteDecimals);	
 	}

 	function marketTitle($marketTitle=null) {
 		return Horus\persistence()->marketTitle($this->marketId(),$marketTitle);	
 	}

 	function maxBuyQuantity(&$portfolio,$assetId) {
 		$portfolioCredit=$portfolio->currencyCredit($this);
 		$quote=$this->assetQuote($assetId);
 		$fixedFees=$this->tradeFixedFeesSum();
 		if ($quote->buyQuote()==0) {
 			//Nano\msg("warning: assetId:$assetId msg:buyQuote is zero");
 			return 0;
 		}
		$quantity=$portfolioCredit/$quote->buyQuote()-$fixedFees;
 		
 		// print_r(["AbsMarket.maxBuyQuantity","portfolioCredit"=>"$portfolioCredit","maxBuyQuantity"=>$quantity,"portfolio",$portfolio]);
		return max($quantity,0);
 	}

 	function maxSellQuantity(&$portfolio,$assetId) {
		return $portfolio->assetQuantity($assetId);
 	}

	function assetTrade(&$portfolio,$assetId,$tradeOp=AssetTradeOperation\AssetBuy,$quantity=1) {
		Asset\checkAssetId($assetId);
		if ($portfolio==null) throw new \exception("portoflio is null");
		$quote=$this->assetQuote($assetId);		
		$portfolioCredit=$portfolio->currencyCredit($this);
		$fixedFees=$this->tradeFixedFeesSum();

		if ($tradeOp==AssetTradeOperation\Buy && $portfolioCredit>=$quantity*$quote->buyQuote()+$fixedFees) {
			$credit=Nano\nanoTextFormatter()->moneyLegend($portfolio->currencyCredit($this));
			//Nano\msg(sprintf("assetTrade op:%s quantity:%s assetId:%s credit:%s quote:%s status:OK", AssetTradeOperation\toCanonical($tradeOp),$quantity,$assetId,$credit,Nano\nanoTextFormatter()->moneyLegend($quote->buyQuote()) ));
			$portfolio->addAssetQuantity($assetId,$quantity,$this);
			$portfolio->removeAssetQuantity($this->defaultExchangeAssetId(),$quantity*$quote->buyQuote()+$fixedFees,$this);
		 	return $quote->buyQuote();
		}

		if ($tradeOp==AssetTradeOperation\Sell && $portfolio->assetQuantity($assetId)>=$quantity) {
			$portfolio->removeAssetQuantity($assetId,$quantity,$this);
			$portfolio->addAssetQuantity($this->defaultExchangeAssetId(),$quantity*$quote->sellQuote()-$fixedFees,$this);
			//Nano\msg(sprintf("assetTrade: op:%s quantity:%s assetId:%s quote:%s  status:OK", AssetTradeOperation\toCanonical($tradeOp),$quantity,$assetId,Nano\nanoTextFormatter()->moneyLegend($quote->sellQuote()) ));
		 	return $quote->sellQuote();
		}

		Nano\msg(sprintf("assetTrade: op:%s quantity:%s assetId:%s quote:%s portfolioCredit:%s status:FAIL", AssetTradeOperation\toCanonical($tradeOp),$quantity,$assetId,$quote,$portfolioCredit ));
		return false;
	}

	abstract function assetQuote($assetId):AssetQuotation\AssetQuotation;
	
	function portfolioValuation($portfolio) {
		$v=0;
		foreach($portfolio->assetIds() as $assetId) {
			$quantity=$portfolio->assetQuantity($assetId);
			$assetQuote=$this->assetQuote($assetId);
			$v+=$quantity*$assetQuote->sellQuote();
		}
		return $v;
	}

	function beat($beat=null) {		
		return Horus\persistence()->marketBeat($this->marketId(),$beat);	
	}

	function nextBeat() {
		Nano\nanoPerformance()->track("market.observeAll");
		$this->beat($this->beat()+1);	
		$this->onBeat()->observeAll($this);	
		Nano\nanoPerformance()->track("market.observeAll");
	}

	function pollContentAgeSeconds() {
		$lastQuotes=$this->marketLastQuotes();
		return (time()-$lastQuotes[0]["pollTime"]);
	}

	function pollContentOutdated() {
		return (($this->pollContentAgeSeconds())>$this->pollContentMaxAgeSeconds);
	}

	function marketStats() {
		return $this->marketStats;
	}

	function marketStatsMedium() {
		return $this->marketStatsMedium;
	}

	function marketStatsLong() {
		return $this->marketStatsLong;
	}

	function save() {	
		exit("save: deferred/deprecated");
	}
}

?>