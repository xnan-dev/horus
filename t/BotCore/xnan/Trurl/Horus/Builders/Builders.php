<?php
namespace xnan\Trurl\Horus\Builders;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\BotArena;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Horus\MarketSchedule;
use xnan\Trurl\Horus\Portfolio;
use xnan\Trurl\Horus\MarketTrader;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HRefs;
use xnan\Trurl\Nano\TextFormatter;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

class Functions { const Load=1; }

function scheduleMathBuild() {
	$m=new MarketSchedule\MarketSchedule();
	$m->title("Cronograma Cryptos");
	$m->marketIsAlwaysOpen(true);
	$m->marketOpenHour("00:00");
	$m->marketCloseHour("00:00");
	$m->marketWeekDaysOpen("0,1,2,3,4,5,6");
	$m->marketBeatSeconds(10);	
	return $m;
}

function scheduleCryptosBuild() {
	$m=new MarketSchedule\MarketSchedule();
	$m->title("Cronograma Cryptos");
	$m->marketIsAlwaysOpen(true);
	$m->marketOpenHour("00:00");
	$m->marketCloseHour("00:00");
	$m->marketWeekDaysOpen("0,1,2,3,4,5,6");
	$m->marketBeatSeconds(10);	
	return $m;
}

function scheduleMervalBuild() {
	$m=new MarketSchedule\MarketSchedule();
	$m->title("Cronograma Merval");
	$m->marketIsAlwaysOpen(false);
	$m->marketOpenHour("11:00");
	$m->marketCloseHour("17:00");
	$m->marketWeekDaysOpen("1,2,3,4,5");
	$m->marketBeatSeconds(10);	
	return $m;
}

function scheduleNasdaqBuild() {	
	$m=new MarketSchedule\MarketSchedule();
	$m->title("Cronograma Nasdaq");
	$m->marketIsAlwaysOpen(false);
	$m->marketOpenHour("9:30");
	$m->marketCloseHour("16:00");
	$m->marketWeekDaysOpen("1,2,3,4,5");
	$m->marketBeatSeconds(10);	
	return $m;
}

function scheduleNyseBuild() {
	$m=scheduleNasdaqBuild();
	$m->title("Cronograma NYSE");
	return $m;
}

function mathArenaBuild($botArenaId="mathArena") {
	$p1=new Portfolio\Portfolio();
	$p2=new PortFolio\Portfolio();
	$p3=new PortFolio\Portfolio();
	
	$p1->portfolioId("botMath1Portfolio");
	$p2->portfolioId("botMath2Portfolio");
	$p3->portfolioId("botMath3Portfolio");
	
	
	$t1=new MarketTrader\DivideAndScaleMarketTrader("botMath1",$p1);
	$t2=new MarketTrader\DivideAndScaleMarketTrader("botMath2",$p2);	
	$t3=new MarketTrader\DivideAndScaleMarketTrader("botMath3",$p3);
	//$t4=new MarketTrader\DivideAndScaleMarketTrader("Scaler1",$p4);
	$omarket=new Market\MathMarket("MathMarket",25);	
	$omarket->marketTitle("Mercado de Prueba");
	$schedule=scheduleMathBuild();
	$omarket->marketSchedule($schedule);
	$market=(HRefs\HRefs::instance())->createHRef();
	$market->set($omarket);

	$p1->addAssetQuantity(Asset\assetUsd()->assetId(),100000,$market,true);
	$p2->addAssetQuantity(Asset\assetUsd()->assetId(),100000,$market,true);
	$p3->addAssetQuantity(Asset\assetUsd()->assetId(),100000,$market,true);

	$t1->setupAutoApprove(false);
	$t2->setupAutoApprove(false);
	$t3->setupAutoApprove(true);
	$t1->setupWaitBeats(25);
	$t2->setupWaitBeats(25);
	$t3->setupWaitBeats(25);
	$t1->traderTitle("Bot Prueba 1");
	$t2->traderTitle("Bot Prueba 2");
	$t3->traderTitle("Bot Prueba 3");
	//$t1->setupNotificationsEnabled(true);

	$t1->setupMarket($market);
	$t2->setupMarket($market);
	$t3->setupMarket($market);


	$a=new BotArena\BotArena($botArenaId,$market);
	$a->addTrader($t1);
	$a->addTrader($t2);
	$a->addTrader($t3);	
	return $a;
}

function yahooFinanceArenaBuild($botArenaId,$botArenaTitle,$botIdPrefix,$botTitlePrefix,$assetCount,$useHistory,$pollerName,$defaultExchangeAssetId,$quoteDecimals,$botCount,$autoApproveCount) {
	
	$arena=yahooFinanceMarketBuild($botArenaId,$assetCount,$botArenaTitle,$useHistory,$botTitlePrefix,$botIdPrefix,$pollerName,$defaultExchangeAssetId,$quoteDecimals);

	yahooFinanceBotsBuild($arena,$botIdPrefix,$botTitlePrefix,$defaultExchangeAssetId,$botCount,$autoApproveCount,$botCount,$autoApproveCount);
	return $arena;
}

function yahooFinanceMarketBuild($botArenaId,$assetCount,$botArenaTitle,$useHistory,$botTitlePrefix,$botIdPrefix,$pollerName,$defaultExchangeAssetId,$quoteDecimals) {	
	if ($useHistory) {
		$omarket=new Market\YahooFinanceTestMarket("$botArenaId"."Market",$assetCount,$pollerName);
	} else {
		$omarket=new Market\YahooFinanceMarket("$botArenaId"."Market",$assetCount,$useHistory,$pollerName);		
	}	
	$omarket->defaultExchangeAssetId($defaultExchangeAssetId);	
	$omarket->marketTitle($botArenaTitle);	
	$schedule=scheduleCryptosBuild();
	$omarket->marketSchedule($schedule);
	$omarket->quoteDecimals($quoteDecimals);
	

	$market= (HRefs\HRefs::Instance())->createHRef();
	$market->set($omarket);

	$a=new BotArena\BotArena($botArenaId,$market,$botIdPrefix,$botTitlePrefix);

	if ($a==null) Nano\nanoCheck()->checkFailed("arena cannot be null");
	if ($a->market()->get()==null) Nano\nanoCheck()->checkFailed("omarket cannot be null");

	return $a;
}

function yahooFinanceBotsBuild(&$arena,$botIdPrefix,$botTitlePrefix,$defaultExchangeAssetId,$botCount,$autoApproveCount) {
	if ($arena==null) Nano\nanoCheck()->checkFailed("arena cannot be null");
	if ($arena->market()->get()==null) Nano\nanoCheck()->checkFailed("omarket cannot be null");

	for ($i=1;$i<=$botCount;$i++) {

		$p=new Portfolio\Portfolio();

		$p->portfolioId(sprintf("%s%s%s%s",$arena->botArenaId(),$botIdPrefix,$i,"Porfolio"));
		$p->addAssetQuantity($defaultExchangeAssetId,100000,$arena->market(),true);
		
		$t=new MarketTrader\DivideAndScaleMarketTrader("$botIdPrefix".$i,$p);

		$t->traderTitle("$botTitlePrefix $i");
		$t->setupWaitBeats(25);	
		$t->setupAutoApprove(($i<=$autoApproveCount));

		$market=$arena->market();
		$t->setupMarket($market);
		
		$market->get()->marketStats()->get()->setupMaxHistoryBeats(25);
		$arena->addTrader($t);
	}
}

function botWorldBuild() {
	throw new \Exception("botWorldBuild unsupported");
	$botWorld=BotWorld\BotWorld::instance();
	$mathArena=mathArenaBuild("mathArena");
	

	for ($i=1;$i<=2;$i++) {
		$live=$i==1;
		$typeSuffix=$live ? "Live" : "Test";
		if ($live) {
			$botCount=3;
			$autoApproveCount=1;
		} else {
			$botCount=2;
			$autoApproveCount=2;			
		}
		//if (!$live) continue;

		$cryptosLiveArena=yahooFinanceArenaBuild("cryptos$typeSuffix"."Arena","Cryptos","bot$typeSuffix","Bot Cryptos en $typeSuffix",15,!$live,"Cryptos","USD",8,$botCount,$autoApproveCount);

		$mervalAccionesGeneralLiveArena=yahooFinanceArenaBuild("mervalAccionesGeneral$typeSuffix"."Arena","Merval Acciones Generales","bot$typeSuffix","Bot Merval Generales en $typeSuffix",15,!$live,"MervalAccionesGeneral","AR$",2,$botCount,$autoApproveCount);
		
		$mervalAccionesLideresLiveArena=yahooFinanceArenaBuild("mervalAccionesLideres$typeSuffix"."Arena","Merval Acciones Líderes","botLive","Bot Líderes en $typeSuffix",15,!$live,"MervalAccionesLideres","AR$",2,$botCount,$autoApproveCount);
		
		$mervalCedearsLiveArena=yahooFinanceArenaBuild("mervalCedears$typeSuffix"."Arena","Merval Cedears","bot$typeSuffix","Bot Cedears en $typeSuffix",15,!$live,"MervalCedears","AR$",2,$botCount,$autoApproveCount);
		
		$nasdaq100liveArena=yahooFinanceArenaBuild("nasdaq100$typeSuffix"."Arena","Nasdaq 100","bot$typeSuffix","Bot Nasdaq 100 en $typeSuffix",15,!$live,"Nasdaq100","USD",4,$botCount,$autoApproveCount);
		
		$snP100liveArena=yahooFinanceArenaBuild("snP100$typeSuffix"."Arena","S&P 100","bot$typeSuffix","Bot S&P 100 en $typeSuffix",15,!$live,"SnP100","USD",4,$botCount,$autoApproveCount);

		$yfForexUsdliveArena=yahooFinanceArenaBuild("yfForexUsd$typeSuffix"."Arena","Forex USD","bot$typeSuffix","Bot Forex USD 100 en $typeSuffix",15,!$live,"YFFOREXUSD","USD",4,$botCount,$autoApproveCount);

		$schedule=scheduleCryptosBuild();
		$cryptosLiveArena->market()->get()->marketSchedule($schedule);
		$schedule=scheduleMervalBuild();
		$mervalAccionesGeneralLiveArena->market()->get()->marketSchedule($schedule);
		$schedule=scheduleMervalBuild();
		$mervalAccionesLideresLiveArena->market()->get()->marketSchedule($schedule);
		$schedule=scheduleMervalBuild();
		$mervalCedearsLiveArena->market()->get()->marketSchedule($schedule);
		$scheduleNasdaqBuild=scheduleCryptosBuild();
		$nasdaq100liveArena->market()->get()->marketSchedule($schedule);
		$scheduleSnpBuild=scheduleCryptosBuild();
		$snP100liveArena->market()->get()->marketSchedule($schedule);		
		$schedule=scheduleCryptosBuild();
		$yfForexUsdliveArena->market()->get()->marketSchedule($schedule);

		$botWorld->addBotArena($mathArena);
		$botWorld->addBotArena($cryptosLiveArena);

		$botWorld->addBotArena($mervalAccionesGeneralLiveArena);
		$botWorld->addBotArena($mervalAccionesLideresLiveArena);
		$botWorld->addBotArena($mervalCedearsLiveArena);
		$botWorld->addBotArena($nasdaq100liveArena);
		$botWorld->addBotArena($snP100liveArena);
		$botWorld->addBotArena($yfForexUsdliveArena);

	}

	return $botWorld;
}
?>