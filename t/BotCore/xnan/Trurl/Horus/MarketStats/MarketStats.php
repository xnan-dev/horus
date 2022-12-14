<?php
namespace xnan\Trurl\Horus\MarketStats;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Nano\TextFormatter;
use xnan\Trurl\Nano\Performance;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Hydra;
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HMatrixes;
use xnan\Trurl\Hydra\HMatrixes\HMatrix;
use xnan\Trurl\Hydra\HMatrixes\HPdoMatrix;

Hydra\Functions::Load;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

class Functions { const Load=1; }

class MarketStats {
	var $marketStatsId;

	var $market;
	var $textFormater;
	var $marketBeatObserver;

	var $mtxScalar;
	var $mtxHistory;

	var $idxAsset=[];
	var $idxScalarDim=[];
	var $idxHistoryDim=[];

	const MarketIndexAsset="@MarketIndex";

	const SValue=0;
	const SMin=1;
	const SMax=2;
	const SAvg=3;
	const SSum=4;
	const SMean=5;
	const SCicle=6;
	const SMinBeat=7;
	const SMaxBeat=8;
	const SCicleBeats=9;
	const SLinearSlope=10;
	
	const SHValue=0;
	const SHCicle=1;


	function __construct(&$market,$marketStatsId) {	//MIG
		if ($marketStatsId==null) Nano\nanoCheck()->checkFailed("marketStatsId cannot be null");
		$this->market=$market;
		$this->marketStatsId=$marketStatsId;
		$this->marketBeatObserver=new MarketBeatObserver($this);
		$this->textFormatter=Nano\newTextFormatter();

		$this->setupIdxAsset(); // always constructed from market.
		$this->setupIdxScalarDim();// always constructed from stats dimensions.
		$this->setupIdxHistoryDim();// always constructed from stats dimensions.

		$this->setupStatsIfReq();

		$market->onBeat()->addObserver($this->marketBeatObserver);		
	}


	private function pdo() { //MIG
		return BotWorld\BotWorld::instance()->pdo();
	}

	function market() { //MIG
		return $this->market;
	}

	function marketId() { //MIG
		return $this->market()->marketId();
	}

	function marketStatsId() {//MIG
		return $this->marketStatsId;
	}

	function marketStatsReset() { //MIG-TODO
		Nano\nanoCheck()->checkFailed("inhab");
		$this->endBeat=0;
		$this->statsValue->reset();
	}

	private function setupIdxAsset() { //MIG-NEW
		$assetIds=$this->assetIds();

		$i=0;
		foreach($assetIds as $assetId) {
			$this->idxAsset[$assetId]=$i;
			++$i;
		}		
	}

	private function setupIdxScalarDim() { //MIG-NEW
		$dims=[ self::SValue,self::SMin,self::SMax,self::SAvg,self::SSum,
				self::SMean,self::SCicle,self::SMinBeat,self::SMaxBeat,self::SCicleBeats,
				self::SLinearSlope];

		$i=0;
		foreach($dims as $dim) {
			$this->idxScalarDim[$dim]=$i;
			++$i;
		}
	}

	private function setupIdxHistoryDim() { //MIG-NEW
		$dims=[self::SHValue,self::SHCicle];

		$i=0;
		foreach($dims as $dim) {
			$this->idxHistoryDim[$dim]=$i;
			++$i;
		}

	}

	private function mtxScalarId() { //MIG-NEW
		return sprintf("mtxMarketStats%s%sScalar",
				ucfirst(str_replace("ArenaMarket","",$this->marketId() )),
				str_replace("marketStats","",$this->marketStatsId() )
			);
	}

	private function mtxHistoryId() { //MIG-NEW
		return sprintf("mtxMarketStats%s%sHistory",
				ucfirst(str_replace("ArenaMarket","",$this->marketId() )),
				str_replace("marketStats","",$this->marketStatsId() )
			);
	}

	private function statsHistoryLastValue($statsDim,$assetId) {
		return $this->statsHistory($statsDim,$assetId,$this->maxHistoryBeats()-1);
	}


	private function statsHistoryShift($dim,$assetId) {
		$this->mtxHistory->shift([$dim,$this->idxAsset[$assetId]]);
	}

	private function setupStatsIfReq() { //MIG

		$dim=[count($this->idxScalarDim),count($this->idxAsset)];
		$this->mtxScalar=new HMatrixes\HPdoMatrix($this->pdo(),$this->mtxScalarId(),$dim);

		$dim=[count($this->idxHistoryDim),count($this->idxAsset),$this->maxHistoryBeats()];
		
		$this->mtxHistory=new HMatrixes\HPdoMatrix($this->pdo(),$this->mtxHistoryId(),$dim);

		$this->mtxScalar->hydrateIfReq();
		$this->mtxHistory->hydrateIfReq();

		if ($this->marketId()=="mathArenaMarket") {

			/*foreach($this->assetIds() as $assetId) {
				printf("debug-statsScalar assetId:%s value:%s\n<br>",
					$assetId,$this->statsScalar(self::SValue,$assetId));
			}*/

		}

		if ($this->mtxScalar->isNew()) {

			foreach ($this->assetIds() as $assetId) {
				$this->statsScalarSet(self::SValue,$assetId,0);
				$this->statsScalarSet(self::SMin,$assetId,$this->infinitePositive());
				$this->statsScalarSet(self::SMax,$assetId,$this->infiniteNegative());
				$this->statsScalarSet(self::SAvg,$assetId,0);
				$this->statsScalarSet(self::SSum,$assetId,0);
				$this->statsScalarSet(self::SMean,$assetId,0);
				$this->statsScalarSet(self::SCicle,$assetId,0);
				$this->statsScalarSet(self::SMinBeat,$assetId,$this->infiniteNegative());
				$this->statsScalarSet(self::SMaxBeat,$assetId,$this->infiniteNegative());
				$this->statsScalarSet(self::SCicleBeats,$assetId,$this->infiniteNegative());
			}
			$this->mtxScalar->dehydrate();
			$this->mtxScalar->hydrate();
		}
		
		if ($this->mtxHistory->isNew()) {

			foreach ($this->assetIds() as $assetId) {
				for ($i=0;$i<$this->maxHistoryBeats();$i++) {				
					$this->statsHistorySet(self::SHValue,$assetId,$i,$this->infiniteNegative());
					$this->statsHistorySet(self::SHCicle,$assetId,$i,$this->infiniteNegative());				
				}
			}
	
			$this->mtxHistory->dehydrate();
			$this->mtxHistory->hydrate();
		}
	}

	function statsScalar($dim,$assetId) { //MIG		
		$coord=[$dim,$this->idxAsset[$assetId]];		
		$ret=$this->mtxScalar->get($coord);
/*		if ($this->marketId()=="mathArenaMarket") {
			printf("statsScalar %s %s assetId:$assetId - dim:$dim coord:%s,%s v:%s\n",
				$this->marketId(),$this->marketStatsId(),$coord[0],$coord[1],$ret);
		}		*/
		
		return $ret;
	}

	function statsHistory($dim,$assetId,$historyIndex) { //MIG
		$coord=[$dim,$this->idxAsset[$assetId],$historyIndex];
		return $this->mtxHistory->get($coord);
	}

	function statsScalarSet($dim,$assetId,$v) { //MIG
		$coord=[$dim,$this->idxAsset[$assetId]];		
		$this->mtxScalar->set($coord,$v);
	}
	
	function statsHistorySet($dim,$assetId,$historyIndex,$v) { //MIG
		$coord=[$dim,$this->idxAsset[$assetId],$historyIndex];
		$this->mtxHistory->set($coord,$v);
		$vv=$this->mtxHistory->get($coord);
		//print "statsHistorySet: dim:$dim assetId:$assetId historyIndex:$historyIndex v:$v vv:$vv\n";
	}

	function synchedBeat($synchedBeat=null) { //MIG
		return Horus\persistence()->msSynchedBeat($this->marketId(),$this->marketStatsId(),$synchedBeat);
	}
	
	function maxHistoryBeats($maxHistoryBeats=null) { //MIG
		return Horus\persistence()->msMaxHistoryBeats($this->marketId(),$this->marketStatsId(),$maxHistoryBeats);
	}

	function beatMultiplier($beatMultiplier=null) { //MIG
		return Horus\persistence()->msBeatMultiplier($this->marketId(),$this->marketStatsId(),$beatMultiplier);
	}

	function textFormatter() { //MIG
		return $this->textFormatter;
	}

	/*
	 *  At which beat the window start (a full window start) given the current end beat.
     *  - The window start keeps moving as beats are processed. 
     *  - If there is a beat multiplier, the start beats gets multiplied for it, 
     *    (eg. for beatMultiplier=10 and maxHistoryBeat=5 , the window positions at beat 50 		are: 0,10,20,30,40,50)
	 */
	function startBeat() { 
		return max(0,
			($this->endBeat()-$this->maxHistoryBeats()*$this->beatMultiplier())
		);
	}

	function endBeat($endBeat=null) { //MIG
		return Horus\persistence()->msEndBeat($this->marketId(),$this->marketStatsId(),$endBeat);
	}

	function isFull() {
		return $this->usageCount() == $this->maxHistoryBeats();
	}

	function usage() {
		return ($this->usageCount()/(double)$this->maxHistoryBeats())*100;
	}

	function firstAssetId() {
		return $this->assetIds()[0];
	}

	function usageCount() {
		$sum=0;
		for($i=0;$i<$this->maxHistoryBeats();$i++) {
		
			$y=$this->statsHistory(self::SHValue,$this->firstAssetId(),$i);
			if ($y==$this->infiniteNegative()) continue; // index not yet assigned.

			$sum+=1;
		}		

		return $sum;
	}

	/*
	 * How many beats are already stored in the window [0..maxHistoryBeats]
	 */
	function beatCount() {
		return $this->usageCount();
	}

	function infinitePositive() { //MIG
		return 1000000000;
	}

	function infiniteNegative() { //MIG
		return -1000000000;
	}	

	function sumHistory($assetId) {	//MIG
		$sum=0;
		$assetIdx=$this->idxAsset[$assetId];

		for($i=0;$i<$this->mtxHistory->lastDimension();$i++) {
			$y=$this->mtxHistory->get([self::SHValue,$assetIdx,$i]);
			if ($y==$this->infiniteNegative()) continue; // index not yet assigned.
			$sum+=$y;
		}		
		return $sum;
	}

	function minHistory($assetId) { //MIG
		$min=$this->infinitePositive();
		$beat=$this->startBeat();
		$minBeat=$beat;

		$assetIdx=$this->idxAsset[$assetId];

		//print "mtxHistory.dimensions:".print_r($this->mtxHistory->dimensions(),true)."\n";
		//print "mtxHistory.lastDimension:".$this->mtxHistory->lastDimension()."\n";

		for($i=0;$i<$this->mtxHistory->lastDimension();$i++) {			
			$value=$this->mtxHistory->get([self::SHValue,$assetIdx,$i]);

			//print "minHistory-find assetId:$assetId i:$i value:$value min:$min minBeat:$minBeat<br>\n";

			if ($value==$this->infiniteNegative()) continue; // index not yet assigned.
			if ($value<$min) $minBeat=$beat;
			$min=min($min,$value);			
			++$beat;
		}				
		return [$min,$minBeat];
	}

	function maxHistory($assetId) {  
		$max=$this->infiniteNegative();
		$beat=$this->startBeat();
		$maxBeat=$beat;

		$assetIdx=$this->idxAsset[$assetId];

		for($i=0;$i<$this->mtxHistory->lastDimension();$i++) {			
			if ($value==$this->infiniteNegative()) continue; // index not yet assigned.
			$value=$this->mtxHistory->get([self::SHValue,$assetIdx,$i]);
			if ($value>$max) $maxBeat=$beat;
			$max=max($max,$value);
			++$beat;
		}

		return [$max,$maxBeat];
	}


	function calcLinearSlope($assetId) {  	
		$xsum=0;
		$ysum=0;
		$xysum=0;
		$x2sum=0;

		$x=1;
				
		for($i=0;$i<$this->maxHistoryBeats();$i++) {
		
			$y=$this->statsHistory(self::SHValue,$assetId,$i);
			if ($y==$this->infiniteNegative()) continue; // index not yet assigned.

			$ysum+=$y;
			$xsum+=$x;
			$x2sum+=($x*$x);
			$xysum+=($x*$y);
			++$x;
		}		

		$r1=(($n*$xysum)-($xsum*$ysum));
		$r2=(($n*$x2sum)-($xsum*$xsum));
		if ($r2!=0) {
			return $r1  / $r2;
		} else {
			return 0;
		}		
	}

	function statsCicle($assetId) { //MIG
		$mean=$this->statsScalar(self::SMean,$assetId);
		$max=$this->statsScalar(self::SMax,$assetId);
		$min=$this->statsScalar(self::SMin,$assetId);
		$value=$this->statsScalar(self::SValue,$assetId);
		$stabilizer=1;
		$cicle=$max!=$min ? 
		(
			(
				($value-$min)
				*$stabilizer
				/(($max-$min)*$stabilizer)
			)*2
		)-1 : 0;				
		return $cicle;
	}
	
	function marketBuyQuoteAvg(&$market) {
		$sum=0;
		$count=0;
		foreach ($market->assetIds() as $assetId) {
			$sum+=$market->assetQuote($assetId)->buyQuote();	
			++$count;
		}
		return $sum/$count;		
	}


	function assetIds() {
		$assetIds=$this->market()->assetIds(); 
		$assetIds[]=self::MarketIndexAsset;

		return $assetIds;
	}

	private function updateStats(&$market) {
		Nano\nanoPerformance()->track("marketStats.updateStats.".$market->marketId());
		$this->endBeat($market->beat());


		$assetIds=$this->assetIds();	

		foreach($assetIds as $assetId) {
			
			if ($assetId==self::MarketIndexAsset) {
				$buyQuote=$this->marketBuyQuoteAvg($market); // indice de mercado: promedio del valor de las acciones que cotizan.
			} else {
				$buyQuote=$market->assetQuote($assetId)->buyQuote();
			}			

			$lastValue=$this->statsHistoryLastValue(self::SHValue,$assetId);

	
			//print "PROCESS ".($this->marketId()).".".($this->marketStatsId())." i:$i\n";

			$this->statsHistoryShift(self::SHValue,$assetId);
			//$this->statsHistory->shift([self::SHCicle,$assetIdx]);

			$this->statsHistorySet(self::SHValue,$assetId,$this->maxHistoryBeats()-1,$buyQuote);
			//$this->statsHistory->set([self::SHCicle,$assetIdx,$i],$this->statsCicle($assetId));

			
			$this->statsScalarSet(self::SValue,$assetId,$buyQuote);						

			//print("debug: statsScalarSet assetId:$assetId buyQuote:$buyQuote\n");

			$this->statsScalarSet(self::SSum,$assetId,$this->sumHistory($assetId));
			$this->statsScalarSet(self::SLinearSlope,$assetId,$this->calcLinearSlope($assetId));
		

			$avg=$this->statsScalar(self::SSum,$assetId)/max(1,$this->usageCount());		
			$this->statsScalarSet(self::SAvg,$assetId,$avg);
			
			$minHistory=$this->minHistory($assetId);
			$maxHistory=$this->maxHistory($assetId);
								
			$this->statsScalarSet(self::SMin,$assetId,$minHistory[0]);
			$this->statsScalarSet(self::SMax,$assetId,$maxHistory[0]);
			$cicle=$this->statsCicle($assetId);
			$this->statsScalarSet(self::SCicle,$assetId,$cicle);			

			$v=(($this->statsScalar(self::SMax,$assetId)
					+$this->statsScalar(self::SMin,$assetId))/2);
			
			$this->statsScalarSet(self::SMean,$assetId,$v);

			$isMin=$this->statsScalar(self::SMin,$assetId)==$buyQuote;
			$isMax=$this->statsScalar(self::SMax,$assetId)==$buyQuote;
			if ($isMin || $isMax) {
				if ($isMin) $this->statsScalarSet(self::SMinBeat,$assetId,$market->beat());
				if ($isMax) $this->statsScalarSet(self::SMaxBeat,$assetId,$market->beat());			
			}

			$minBeat=$this->statsScalar(self::SMinBeat,$assetId);
			$maxBeat=$this->statsScalar(self::SMinBeat,$assetId);
			$min=$this->statsScalar(self::SMin,$assetId);
			$max=$this->statsScalar(self::SMax,$assetId);
	
	//		print "assetId:$assetId minBeat:$minBeat maxBeat:$maxBeat buyQuote:$buyQuote min:$min|$minHistory[0] max:$max|$maxHistory[0] cicle:".number_format($cicle,2)." i:$i\n";

			if ($isMin || $isMax) $this->statsScalarSet(self::SCicleBeats,$assetId,
					abs(
						$this->statsScalar(self::SMaxBeat,$assetId)-
						$this->statsScalar(self::SMinBeat,$assetId)
					)
				);			
		}

		Nano\nanoPerformance()->track("marketStats.updateStats.".$market->marketId());
	}

	function onBeat($market) {  

		//printf("beat %s synchedBeat %s stats:%s<br>",$market->beat(),$this->synchedBeat,$this->marketStatsId);
		//echo sprintf("marketStats: %s beatSkip: beatMultiplier:%s onBeat: %s\n",
		//	$this->marketStatsId(),$this->beatMultiplier(),$market->beat());
		//printf("marketStats: %s beatMultiplier: %s synchedBeat: %s\n",
		//	$this->marketStatsId(),$this->beatMultiplier(),$this->synchedBeat());

		$this->setupStatsIfReq();

		if (  $this->beatMultiplier()>1 && 
			(($market->beat() % $this->beatMultiplier()) != 0) ) {

			// beat should be skipped.
		
		} else if ($market->beat()==$this->synchedBeat()) {
			// market already synched.
		} else {
			$this->updateStats($market);
			$this->synchedBeat($market->beat());
		}

		$this->mtxScalar->dehydrate();
		$this->mtxHistory->dehydrate();
	}

}

class MarketBeatObserver extends Observer\Observer { //MIG
	var $marketStats;

	public function __construct(&$marketStats) {
		$this->marketStats=$marketStats;
	}

	public function observe(&$onBeat,&$market) {	
		//$this->marketStats->markChanged();		
		$this->marketStats->onBeat($market);		
	}

}

?>