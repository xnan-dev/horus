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

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

class Functions { const Load=1; }

class MarketStats {
	var $market;
	var $marketStatsId;
	var $marketBeatObserver;
	var $textFormater;

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

	function __construct(&$market,$marketStatsId) {		
		if ($marketStatsId==null) Nano\nanoCheck()->checkFailed("marketStatsId cannot be null");
		$this->market=$market;
		$this->marketStatsId=$marketStatsId;
		$this->marketBeatObserver=new MarketBeatObserver($this);
		$this->textFormatter=Nano\newTextFormatter();
		$market->onBeat()->addObserver($this->marketBeatObserver);		
	}


	private function pdo() {
		return BotWorld\BotWorld::instance()->pdo();
	}

	function market() {
		return $this->market;
	}

	function marketStatsId() {
		return $this->marketStatsId;
	}

	private function isMarketStatsNew() {
		$query=sprintf(
				"SELECT COUNT(*) as count FROM marketStats WHERE marketId='%s' AND marketStatsId='%s'",
					$this->market()->marketId(),
					$this->marketStatsId);				

		$r=$this->pdo()->query($query);
		
		$row=$r->fetch();
		return $row["count"]>0;
	}

	function marketStatsReset() {
		$delQuery1=sprintf(
				"DELETE FROM marketStatsLog WHERE marketId='%s' AND marketStatsId='%s'",
					$this->market()->marketId(),
					$this->marketStatsId);		

		$delQuery2=sprintf(
				"DELETE FROM marketStats WHERE marketId='%s' AND marketStatsId='%s'",
					$this->market()->marketId(),
					$this->marketStatsId);		
		
		$r=$this->pdo()->query($delQuery1);
		$r=$this->pdo()->query($delQuery2);
	}

	private function rowMarketStats() {
		$query=sprintf(
		"SELECT * FROM marketStats WHERE marketId='%s' AND marketStatsId='%s'",
			$this->market()->marketId(),
			$this->marketStatsId);		

		$r=$this->pdo()->query($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("marketStats row not found");
		}		
	}

	private function fieldMarketStats($field) {
		return $this->rowMarketStats()[$field];
	}

	private function fieldMarketStatsSetInt($field,$value) {
		$query=sprintf(
			"UPDATE marketStats SET $field=$value WHERE marketId='%s' AND marketStatsId='%s'",
				$this->market()->marketId(),
				$this->marketStatsId);		

		$this->pdo()->query($query);
	}

	function statsScalarSet($statsDim,$assetId,$statsValue) {

		$delQuery=sprintf(
				"DELETE FROM marketStatsLog WHERE marketId='%s'
					 AND marketStatsId='%s'
					 AND assetId='%s'
					 AND statsDim='%s'
					 AND historyIndex IS NULL
					 ",
					$this->market()->marketId(),
					$this->marketStatsId,
					$assetId,
					$statsDim);		
		

		$query=sprintf(
				"INSERT INTO marketStatsLog(marketId,marketStatsId,assetId,statsDim,statsValue) 
				VALUES('%s','%s','%s',%s,%s)",
					$this->market()->marketId(),
					$this->marketStatsId,
					$assetId,
					$statsDim,					
					$statsValue);		
		
		$r=$this->pdo()->query($delQuery);
		$r=$this->pdo()->query($query);
	}

	function statsHistorySet($statsDim,$assetId,$historyIndex,$statsValue) {

		$delQuery=sprintf(
				"DELETE FROM marketStatsLog WHERE marketId='%s'
					 AND marketStatsId='%s'
					 AND assetId='%s'
					 AND statsDim='%s'
					 AND historyIndex=%s
					 ",
					$this->market()->marketId(),
					$this->marketStatsId,
					$assetId,
					$statsDim,
					$historyIndex);		

		$query=sprintf(
				"INSERT INTO marketStatsLog(marketId,marketStatsId,assetId,statsDim,historyIndex,statsValue) 
				VALUES('%s','%s','%s',%s,%s,%s)",
					$this->market()->marketId(),
					$this->marketStatsId,
					$assetId,
					$statsDim,					
					$historyIndex,
					$statsValue);		

		$r=$this->pdo()->query($delQuery);
		$r=$this->pdo()->query($query);
	}

	private function setupMarketStatsHead() {

		$delQuery=sprintf(
				"DELETE FROM marketStats WHERE marketId='%s' AND marketStatsId='%s'",
					$this->market()->marketId(),
					$this->marketStatsId);		

		$query=sprintf(
				"INSERT INTO marketStats(marketId,marketStatsId,endBeat,
						maxHistoryBeats,synchedBeat,beatMultiplier) 
					VALUES ('%s','%s',%s,%s,%s,%s)",
					$this->market()->marketId(),
					$this->marketStatsId,
					0,100,-1,1);		


		$this->pdo()->query($query);
	}


	private function setupMarketStatsLog() {
		
		foreach ($this->market()->assetIds() as $assetId) {
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
			$this->statsScalarSet(self::SLinearSlope,$assetId,0);
		}

		foreach ($this->market()->assetIds() as $assetId) {
			for ($i=0;$i<$this->settingMaxHistoryBeats;$i++) {				
				$this->statsHistorySet(self::SHValue,$assetId,$i,$this->infiniteNegative());
				$this->statsHistorySet(self::SHCicle,$assetId,$i,$this->infiniteNegative());	
			}
		}

	}

	private function setupMarketStats() {				
		$this->setupMarketStatsHead();
		$this->setupMarketStatsLog();
	}

	function statsScalar($statsDim,$assetId) {

		$query=sprintf(
			"SELECT * 
				FROM marketStatsLog
				WHERE
					marketId='%s' AND
					marketStatsId='%s' AND
					statsDim=%s AND
					assetId='%s'",
			$this->market()->marketId(),
			$this->marketStatsId,
			$statsDim,
			$assetId);		

		$r=$this->pdo()->query($query);		

		if ($row=$r->fetch()) {
			return $row["statsValue"];
		}

		Nano\nanoCheck()->checkFailed("stats not found");
	}


	function synchedBeat() {
		return $this->fieldMarketStats("synchedBeat");
	}
	
	function maxHistoryBeats($maxHistoryBeats=null) {
		if ($maxHistoryBeats!=null) {
			$this->fieldMarketStatsSetInt("maxHistoryBeats",$maxHistoryBeats);
		}
		return $this->fieldMarketStats("maxHistoryBeats");
	}

	function beatMultiplier($beatMultiplier=null) {
		if ($beatMultiplier!=null) {
			$this->fieldMarketStatsSetInt("beatMultiplier",$beatMultiplier);
		}
		return $this->fieldMarketStats("beatMultiplier");
	}

	function textFormatter() {
		return $this->textFormatter;
	}

	function startBeat() {		
		return max(0,$this->endBeat()-$this->maxHistoryBeats()+1);
	}

	function endBeat() {
		return $this->fieldMarketStats("endBeat");
	}

	function beatCount() {
		return $this->endBeat()-$this->startBeat()+1;
	}

	function infinitePositive() {
		return 1000000000;
	}

	function infiniteNegative() {
		return -1000000000;
	}	

	function sumHistory($assetId) {		
		$sum=0;
		$assetIdx=$this->statsAssetIndex->get($assetId);

		for($i=0;$i<$this->statsHistory->lastDimension();$i++) {
			$sum+=$this->statsHistory->get([$this->SHValue,$assetIdx,$i]);
		}		
		return $sum;
	}

	function calcLinearSlope($assetId) {		
		$xsum=0;
		$ysum=0;
		$xysum=0;
		$x2sum=0;

		$x=1;
		$assetIdx=$this->statsAssetIndex->get($assetId);		
		$n=$this->statsHistory->lastDimension();
		
		for($i=0;$i<$n;$i++) {
			$y=$this->statsHistory->get([$this->SHValue,$assetIdx,$i]);
			$ysum+=$y;
			$xsum+=$x;
			$x2sum+=($x*$x);
			$xysum+=($x*$y);
			++$x;
		}		

		return (($n*$xysum)-($xsum*$ysum))  / 
			(($n*$x2sum)-($xsum*$xsum));
	}

	function minHistory($assetId) {
		$min=$this->infinitePositive();
		$beat=$this->startBeat();
		$minBeat=$beat;
		$assetIdx=$this->statsAssetIndex->get($assetId);

		for($i=0;$i<$this->statsHistory->lastDimension();$i++) {
			$value=$this->statsHistory->get([self::SHValue,$assetIdx,$i]);
			if ($value==$this->infiniteNegative()) break;
			if ($value<$min) $minBeat=$beat;
			$min=min($min,$value);
			//print "minHistory-find assetId:$assetId i:$i value:$value min:$min minBeat:$minBeat<br>\n";
			++$beat;
		}				
		return [$min,$minBeat];
	}

	function maxHistory($assetId) {
		$max=$this->infiniteNegative();
		$beat=$this->startBeat();
		$maxBeat=$beat;

		$assetIdx=$this->statsAssetIndex->get($assetId);

		for($i=0;$i<$this->statsHistory->lastDimension();$i++) {			
			if ($value==$this->infiniteNegative()) break;
			$value=$this->statsHistory->get([self::SHValue,$assetIdx,$i]);
			if ($value>$max) $maxBeat=$beat;
			$max=max($max,$value);
			++$beat;
		}

		return [$max,$maxBeat];
	}


	function statsCicle($assetId) {		
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

	function setupStatsIfReq() {
		//print "SETUP IFREQ maxHistoryBeats:$this->settingMaxHistoryBeats<br>\n";
		if ($this->isMarketStatsNew()) $this->setupMarketStats();
	}

	function marketBuyQuoteAvg(&$market) {
		$sum=0;
		$count=0;
		foreach ($market->assetIds()->values()	as $assetId) {
			$sum+=$market->assetQuote($assetId)->buyQuote();	
			++$count;
		}
		return $sum/$count;		
	}

	function onBeat($market) {
		$this->setupStatsIfReq();
		if (  $this->beatMultiplier()>1 && 
			(($market->beat() % $this->beatMultiplier()) != 0) ) {
			echo "ONBEAT-ESQUIVADO: $this->beatMultiplier on beat: $market->beat\n";
			return; // saltamos los beats que se deben ignorar de acuerdo al multiplicador.
		}

		//printf("beat %s synchedBeat %s stats:%s<br>",$market->beat(),$this->synchedBeat,$this->marketStatsId);
		if ($market->beat()==$this->synchedBeat) return;		

		Nano\nanoPerformance()->track("marketStats.onBeat.".$market->marketId());
		$this->endBeat=$market->beat();				
		
		$assetIds=$this->statsAssetIndex->keys(); // includes derivaed assets.  instead of $market->assetIds()->values()		

		foreach($assetIds as $assetId) {
			$assetIdx=$this->statsAssetIndex->get($assetId);
			
			if ($assetId==self::MarketIndexAsset) {
				$buyQuote=$this->marketBuyQuoteAvg($market); // indice de mercado: promedio del valor de las acciones que cotizan.
			} else {
				$buyQuote=$market->assetQuote($assetId)->buyQuote();
			}			
			
			$i=($this->beatCount()-1) / $this->beatMultiplier();

			$lastValue=$this->statsHistory->get([self::SHValue,$assetIdx,$this->settingMaxHistoryBeats-1]);

	
			if ($this->beatCount()==$this->settingMaxHistoryBeats && 
					$lastValue!=
					$this->infiniteNegative()) {				
				$this->statsHistory->shift([self::SHValue,$assetIdx]);
				//$this->statsHistory->shift([self::SHCicle,$assetIdx]);
			}

	
			$this->statsHistory->set([self::SHValue,$assetIdx,$i],$buyQuote /*33+$this->market->get()->beat()*/);

			//$this->statsHistory->set([self::SHCicle,$assetIdx,$i],$this->statsCicle($assetId));
			
			$this->statsScalar->set([self::SValue,$assetIdx],$buyQuote);						
			$this->statsScalar->set([self::SSum,$assetIdx],$this->sumHistory($assetId));
			$this->statsScalar->set([self::SLinearSlope,$assetIdx],$this->calcLinearSlope($assetId));
		

			$avg=$this->statsScalar->get([self::SSum,$assetIdx])/$this->beatCount();			
			$this->statsScalar->set([self::SAvg,$assetIdx],$avg);
			
			$minHistory=$this->minHistory($assetId);
			$maxHistory=$this->maxHistory($assetId);
									
			$this->statsScalar->set([self::SMin,$assetIdx],$minHistory[0]);
			$this->statsScalar->set([self::SMax,$assetIdx],$maxHistory[0]);
			$cicle=$this->statsCicle($assetId);
			$this->statsScalar->set([self::SCicle,$assetIdx],$cicle);			

			$v=(($this->statsScalar->get([self::SMax,$assetIdx])
					+$this->statsScalar->get([self::SMin,$assetIdx]))/2);
			
			$this->statsScalar->set([self::SMean,$assetIdx],$v);

			$isMin=$this->statsScalar(self::SMin,$assetId)==$buyQuote;
			$isMax=$this->statsScalar(self::SMax,$assetId)==$buyQuote;
			if ($isMin || $isMax) {
				if ($isMin) $this->statsScalar->set([self::SMinBeat,$assetIdx],$market->beat());
				if ($isMax) $this->statsScalar->set([self::SMaxBeat,$assetIdx],$market->beat());			
			}

			$minBeat=$this->statsScalar->get([self::SMinBeat,$assetIdx]);
			$maxBeat=$this->statsScalar->get([self::SMinBeat,$assetIdx]);
			$min=$this->statsScalar->get([self::SMin,$assetIdx]);
			$max=$this->statsScalar->get([self::SMax,$assetIdx]);
	//		print "assetId:$assetId minBeat:$minBeat maxBeat:$maxBeat buyQuote:$buyQuote min:$min|$minHistory[0] max:$max|$maxHistory[0] cicle:".number_format($cicle,2)." i:$i\n";

			if ($isMin || $isMax) $this->statsScalar->set([self::SCicleBeats,$assetIdx],
					abs(
						$this->statsScalar->get([self::SMaxBeat,$assetIdx])-
						$this->statsScalar->get([self::SMinBeat,$assetIdx])
					)
				);
		}


		$this->synchedBeat=$market->beat();
		echo "ONBEAT-ACA $this->marketStatsId : $this->beatMultiplier $this->synchedBeat\n";

		Nano\nanoPerformance()->track("marketStats.onBeat.".$market->marketId());
	}

	/*function assetIndex($assetId) {
		$index=0;
		foreach($this->statsValue->keys() as $candidateAssetId) {
			$value=$this->statsValue->get($candidateAssetId);
			if ($assetId==$candidateAssetId) return $index;
			++$index;
		}
		return -1;
	}	*/

	/*function mapAsJsonList($map,$xName,$yName) {
			$x="";
			$y="";
			foreach($map as $key=>$value) {
			if (strlen($x)>0) $x.=",";
			if (strlen($y)>0) $y.=",";
			$x.=$key;
			$y.=$value;

		}		

		$json=sprintf('{"%s":[%s],"%s":[%s]}',$xName,$x,$yName,$y);		
		return $json;
	}*/

	/*function arrayAsJsonList($array,$xcount=100) {
		$x="";
		$windowCount=min(count($array),$xcount);
		for ($i=0;$i<$windowCount;$i++) {
			if (strlen($x)>0) $x.=",";
			$value=$array[count($array)-$windowCount+$i];
			$x.=$value;
		}

		$json=sprintf('[%s]',$x);		
		return $json;
	}*/
	
	/*function marketHistoryLabels($xcount=100) {
		if (count($this->statsValueHistory() )==0) return $this->arrayAsJsonList([],$xcount); 
		$firstAssetId=array_keys($this->statsValueHistory)[0];
		return $this->arrayAsJsonList(array_keys($this->statsValueHistory[$firstAssetId]),$xcount);
	}*/

	/*function marketHistoryDatasetJson($assetId,$valueHistory,$xcount=100,$label=null,$dash=false,$color=null) {		
		$colors=array("rgb(200,150,150)","rgb(150,200,150)","rgb(0,0,200)","rgb(200,200,0)","rgb(0,200,200)","rgb(200,0,200)","rgb(200,200,200)");
		if ($color==null) $color=$colors[$this->assetIndex($assetId)%(count($colors)-1)];
		$extra=$dash ? ",borderDash: [5, 5]" : "";

		return sprintf("{label:'%s' $extra,fill: false,backgroundColor: '%s',borderColor: '%s',data: %s }",$label!=null ? $label : $assetId,$color,$color,$this->arrayAsJsonList($valueHistory,$xcount));
	}*/

	/*function marketHistoryDatasetsJson($xcount=100) {
		$json="";
		foreach($this->statsCicleHistory() as $assetId=>$valueHistory) {
			if ($assetId==$this->market->defaultExchangeAssetId()) continue;
			//if ($assetId!="BTC-USD") continue;
			if (strlen($json)>0) $json.=",";
			$json.=$this->marketHistoryDatasetJson($assetId,$valueHistory,$xcount);
		}
		return sprintf("[%s]",$json);
	}*/

	/*function marketHistoryAsJson($xcount=100) {		
		 return sprintf("{ labels: %s, datasets: %s }",$this->marketHistoryLabels($xcount), $this->marketHistoryDatasetsJson($xcount));
	}*/
}

class MarketBeatObserver extends Observer\Observer {
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