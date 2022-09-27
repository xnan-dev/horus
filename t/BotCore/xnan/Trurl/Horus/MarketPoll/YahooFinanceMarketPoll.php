<?php

namespace xnan\Trurl\Horus\MarketPoll;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Horus\AssetQuotation;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano\Observer;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Nano\DataSet;

use Scheb\YahooFinanceApi\ApiClient;
use Scheb\YahooFinanceApi\ApiClientFactory;
use GuzzleHttp\Client;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

//Uses: End

Trurl\Functions::Load;
Asset\Functions::Load;
AssetTradeOperation\Functions::Load;

set_time_limit(120*10);

class YahooFinanceMarketPoll {
	var $dsMarketHistory;
	var $pollerName;
	var $pdo;

	function __construct($pollerName) {
		$this->pollerName=$pollerName;
	}

	function pdo($pdo=null) {
		if ($pdo!=null) {
			$this->pdo=$pdo;
		}
		return $this->pdo;
	}


	function pollerName() {
		return $this->pollerName;
	}

	function pollFolder() {
		return sprintf('content/poll/YahooFinance/%s',$this->pollerName());
	}

	function pollCsvFile($fileName) {
		return sprintf("%s/%s",$this->pollFolder(),$fileName);
	}

	function assetsAsCsv() {
		 	$assets=file_get_contents($this->pollCsvFile("assets.csv"));
			return $assets;
	}

	function assets() {
			$assets=Nano\nanoCsv()->csvToArray($this->pollCsvFile("assets.csv"));						
			return $assets;
	}

	function filterLines(&$history,$firstBeat,$lastBeat) {
		$rows=Nano\nanoCsv()->csvContentToArray($history,";");	

		$retRows=[];
		foreach($rows as $row) {
			if ($row["marketBeat"]>=$firstBeat && $row["marketBeat"]<=$lastBeat) {
				$retRows[]=$row;
			}
			if ($row["marketBeat"]>$lastBeat) break;
		}

		$header=explode(',','marketBeat,assetId,assetName,open,high,low,close,adjClose,date,dateTimeZoneGmt');
		$ds=new DataSet\DataSet($header);

		foreach($retRows as $row) {
			$ds->addRow($row);
		}


		return $ds->toCsvRet();
	}

	function marketHistoryAsCsv($page,$pageSize) {
		$marketHistory=file_get_contents($this->pollCsvFile("marketHistory.csv"));
		$marketHistory=$this->filterLines($marketHistory,$pageSize*$page+0,$pageSize*$page+$pageSize-1);
		return  $marketHistory;		
	}

	function marketQuotesAsCsv() {
		$ds=new DataSet\DataSet($this->marketQuotesHeader());
		$sql=sprintf("SELECT * FROM %s ORDER BY marketBeat ASC",$this->pollQuotesTable());
		$r=$this->pdoQuery($sql);
		while ($row=$r->fetch()) {
			$row2=[];
			$row2["marketBeat"]=$row["marketBeat"];
			$row2["assetId"]=$row["assetId"];
			$row2["buyQuote"]=$row["buyQuote"];
			$row2["sellQuote"]=$row["sellQuote"];
			$row2["reportedDate"]=$row["reportedDate"];
			$row2["pollTime"]=$row["pollTime"];
			$row2["pollDate"]=$row["pollDate"];
			$ds->addRow($row2);			
		}
		return  $ds->toCsvRet();
	}

	function marketLastQuotesAsCsv() {
		$ds=new DataSet\DataSet($this->marketQuotesHeader());
		$sql=sprintf("SELECT * FROM %s",$this->pollLastQuotesTable());
		$r=$this->pdoQuery($sql);
		while ($row=$r->fetch()) {
			$row2=[];
			$row2["marketBeat"]=$row["marketBeat"];
			$row2["assetId"]=$row["assetId"];
			$row2["buyQuote"]=$row["buyQuote"];
			$row2["sellQuote"]=$row["sellQuote"];
			$row2["reportedDate"]=$row["reportedDate"];
			$row2["pollTime"]=$row["pollTime"];
			$row2["pollDate"]=$row["pollDate"];
			$ds->addRow($row2);			
		}
		return  $ds->toCsvRet();		
	}

	function marketLastQuotesClean() {
		$ds=new DataSet\DataSet($this->marketQuotesHeader());
		$sql=sprintf(sprintf("TRUNCATE %s",$this->pollLastQuotesTable() ));
		$r=$this->pdoQuery($sql);
	}

	function marketQuotes() {
		$ds=new DataSet\DataSet($this->marketQuotesHeader());
		$sql=sprintf("SELECT * FROM %s ORDER BY marketBeat ASC",$this->pollQuotesTable());
		$r=$this->pdoQuery($sql);
		$rows=[];
		while ($row=$r->fetch()) {
			$row2=[];
  	 	 	$row2["marketBeat"]=$row["marketBeat"];
			$row2["assetId"]=$row["assetId"];
			$row2["buyQuote"]=$row["buyQuote"];
			$row2["sellQuote"]=$row["sellQuote"];
			$row2["reportedDate"]=$row["reportedDate"];
			$row2["pollTime"]=$row["pollTime"];
			$row2["pollDate"]=$row["pollDate"];
	
			$rows[]=$row2;
		}
		return  $rows;
	}

	function marketLastQuotes() {
		$ds=new DataSet\DataSet($this->marketQuotesHeader());
		$sql=sprintf("SELECT * FROM %s",$this->pollLastQuotesTable());
		$r=$this->pdoQuery($sql);
		$rows=[];
		while ($row=$r->fetch()) {
			$row2=[];
  	 	 	$row2["marketBeat"]=$row["marketBeat"];
			$row2["assetId"]=$row["assetId"];
			$row2["buyQuote"]=$row["buyQuote"];
			$row2["sellQuote"]=$row["sellQuote"];
			$row2["reportedDate"]=$row["reportedDate"];
			$row2["pollTime"]=$row["pollTime"];
			$row2["pollDate"]=$row["pollDate"];
	
			$rows[]=$row2;
		}
		return  $rows;
	}

	function historicalDataToArray($historicalData,$assetId,$assetName) {
		$res=array();
		$beat=0;

		foreach($historicalData as $row) {
			$rowRes=array();
			$rowRes["marketBeat"]=$beat;
			$rowRes["assetId"]=$assetId;
			$rowRes["assetName"]=$assetName;
			$rowRes["open"]=$row->getOpen();
			$rowRes["high"]=$row->getHigh();
			$rowRes["low"]=$row->getLow();
			$rowRes["close"]=$row->getClose();
			$rowRes["adjClose"]=$row->getAdjClose();
			$rowRes["date"]=date_format($row->getDate(),'Y-m-d H:i:s');
			$rowRes["dateTimeZoneGmt"]=$row->getDate()->getTimezone()->getOffset($row->getDate());
			$res[]=$rowRes;
			++$beat;
		}
		return $res;
	}


	function queryAssetHistory(&$client,$assetId,$assetName) {
		$days=365*2;
		// $quote = $client->getQuote($assetId);
		// print "$assetID-usd:".$quote->getAsk();

		$historicalData = $client->getHistoricalQuoteData( // Returns an array of Scheb\YahooFinanceApi\Results\HistoricalData
			    $assetId,
		    ApiClient::INTERVAL_1_DAY,
		    new \DateTime("-$days days"),
		    new \DateTime("today")
		);

		$historicalDataArray=$this->historicalDataToArray($historicalData,$assetId,$assetName);
		// print "<pre>";
		// print_r($historicalDataArray);
		// print "</pre>";
		return $historicalDataArray;
	}


 	function pollHistory() {
 		Nano\msg(sprintf("YahooFinanceMarketPoll: pollerName:%s pollHistory time:%s marketBeat:%s",$this->pollerName(),time(),$this->market->beat() ));

		$assets=$this->assets();

		$client = ApiClientFactory::createApiClient();

		$index=0;
		$assetsHistory=array();
		foreach($assets as $asset) {
			$assetId=$asset["assetId"];
			$assetName=$asset["assetName"];
			print "assetId:$assetId assetName:$assetName<br>";
			$assetHistory=$this->queryAssetHistory($client,$assetId,$assetName);
			$assetsHistory=array_merge($assetsHistory,$assetHistory);
			//if ($index>1) break;
			++$index;
		}
		usort($assetsHistory,'xnan\Trurl\Horus\MarketPoll\YahooFinanceMarketPoll::beatCompare');

		$header=explode(',','marketBeat,assetId,assetName,open,high,low,close,adjClose,date,dateTimeZoneGmt');
		$dsMarketHistory=new DataSet\DataSet($header);

		foreach($assetsHistory as $assetHistory) {
			$dsMarketHistory->addRow($assetHistory);
		}
		$dsMarketHistory->toCsv($this->pollCsvFile("marketHistory.csv"));
		// print '<pre>';
		// print_r($assetsHistory);
		// print '</pre>';
 	}


	function beatCompare($a,$b) {
	/*	print "a:";
		print_r($a);
		print "b:";
		print_r($b);
	*/		
		if ($a['marketBeat'] == $b['marketBeat']) {
	       if ($a['assetId'] == $b['assetId']) return 0;
	       return ($a['assetId'] < $b['assetId']) ? -1 : 1;
	    }
	    return ($a['marketBeat'] < $b['marketBeat']) ? -1 : 1;
	}

	function assetIds($assets) {
		$assetIds=array();
		foreach($assets as $asset) {			
			$assetIds[]=$asset["assetId"];
		}
		return $assetIds;
	}

	function nextQuotesBeat() {		
		return $this->lastQuotesBeat()+1;
	}

	function lastQuotesBeat() {
/*		$lastQuotes=$this->marketLastQuotes();
		if (count($lastQuotes)>0) {
			return $lastQuotes[0]["marketBeat"];
		} else {
			return 0;
		}		*/

		$query=sprintf("SELECT max(marketBeat)+1 as nextMarketBeat FROM %s",$this->pollQuotesTable());
		$r=$this->pdoQuery($query);
		if ($row=$r->fetch()) {
			return $row["nextMarketBeat"];
		}
		return 0;
	}

	function marketQuotesHeader() {
		return array("marketBeat","assetId","buyQuote","sellQuote","reportedDate","pollTime","pollDate");
	}

	function pollQuotes($beats=1,$beatSleep=0) {
		for($t=0;$t<$beats;$t++) {
			
			$lastBeat=$t==($beats-1);
			$this->pollQuotesOnBeat($beats,$beatSleep);

			if ($beats>1 && $beatSleep>0) { 
				Nano\msg(sprintf("YahooFinanceMarketPoll: pollQuote: sleep $beatSleep"));
				usleep(1000000*$beatSleep);
			}
		}
	}

	function pollQuotesTable() {
		return sprintf("marketQuotes%s",ucfirst($this->pollerName() ));
	}

	function pollLastQuotesTable() {
		return sprintf("marketLastQuotes%s",ucfirst($this->pollerName() ));
	}

	public function pollQuotesCreateTableIfReq() {
		$sql=sprintf("CREATE TABLE IF NOT EXISTS `%s` (
				`marketBeat` BIGINT(20) NOT NULL,
				`assetId` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
				`buyQuote` DECIMAL(20,10) NULL DEFAULT NULL,
				`sellQuote` DECIMAL(20,10) NULL DEFAULT NULL,
				`reportedDate` DATETIME NULL DEFAULT NULL,
				`pollTime` BIGINT(20) NULL DEFAULT NULL,
				`pollDate` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`marketBeat`, `assetId`) USING BTREE
			)
			COLLATE='utf8mb4_general_ci'
			ENGINE=InnoDB",$this->pollQuotesTable() );

		$this->pdoQuery($sql);
		
	}

	public function pollLastQuotesCreateTableIfReq() {
		$sql=sprintf("CREATE TABLE IF NOT EXISTS `%s` (
				`marketBeat` BIGINT(20) NOT NULL,
				`assetId` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
				`buyQuote` DECIMAL(20,10) NULL DEFAULT NULL,
				`sellQuote` DECIMAL(20,10) NULL DEFAULT NULL,
				`reportedDate` DATETIME NULL DEFAULT NULL,
				`pollTime` BIGINT(20) NULL DEFAULT NULL,
				`pollDate` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`marketBeat`, `assetId`) USING BTREE
			)
			COLLATE='utf8mb4_general_ci'
			ENGINE=InnoDB",$this->pollLastQuotesTable() );

		$this->pdoQuery($sql);		
	}

	function pollQuoteAdd($row) {		
		$query=sprintf("
				INSERT INTO %s(marketBeat,assetId,buyQuote,sellQuote,reportedDate,pollTime,pollDate)
				VALUES (%s,'%s',%s,%s,'%s',%s,'%s')",
			$this->pollQuotesTable(),
			$row["marketBeat"],
			$row["assetId"],
			round($row["buyQuote"],10),
			round($row["sellQuote"],10),
			$row["reportedDate"],
			$row["pollTime"],
			$row["pollDate"]
			);
		
		$this->pdoQuery($query);
	}

	function pollLastQuoteAdd($row) {		
		$query=sprintf("
				INSERT INTO %s(marketBeat,assetId,buyQuote,sellQuote,reportedDate,pollTime,pollDate)
				VALUES (%s,'%s',%s,%s,'%s',%s,'%s')",
			$this->pollLastQuotesTable(),
			$row["marketBeat"],
			$row["assetId"],
			round($row["buyQuote"],10),
			round($row["sellQuote"],10),
			$row["reportedDate"],
			$row["pollTime"],
			$row["pollDate"]
			);
		
		$this->pdoQuery($query);
	}

	private function pdoQuery($query) {
		$r=$this->pdo()->query($query);		
		if ($r===false) Nano\nanoCheck()->checkFailed("pdoQuery: failed to execute query.\nquery:$query");
		return $r;
	}


	function pollQuotesOnBeat($beats=1,$beatSleep=0) {

		$this->pollQuotesCreateTableIfReq();
		$this->pollLastQuotesCreateTableIfReq();
		
		$marketBeat=$this->nextQuotesBeat();
		$timeObj=new \DateTime();
		$time=time();

		$pollDate=date_format($timeObj,'Y-m-d H:i:s');			


		$client = ApiClientFactory::createApiClient();
		$assets=$this->assets();
		//print_r($assets);
		$assetIds=$this->assetIds($assets);

		//print_r($assetIds);
		$quotes = $client->getQuotes($assetIds);


		//$quote=$quotes[0];
		//print_r("<pre>");
		//print_r($quote);
		//print_r("</pre>");
		Nano\msg(sprintf("YahooFinanceMarketPoll: poll pollDate:%s marketBeat:%s",$pollDate,$marketBeat ));		
		$dsMarketQuotes=new DataSet\DataSet($this->marketQuotesHeader());
		$newLines=0;
		$lastQuotes=$this->marketLastQuotes();
		$lastQuote=count($lastQuotes)>0 ? $lastQuotes[0] : null;
		//print_r($lastQuotes[0]);

		$lastQuotesClean=false;

		foreach($quotes as $quote) {
			//printf("************* quote :%s\n",$quote->getSymbol());
			$regTime=$quote->getRegularMarketTime();
			//print_r($quote);

			if ($regTime!=null) $regTime=$regTime->setTimeZone(new \DateTimeZone('America/Argentina/Buenos_Aires'));
			$reportedDate=$regTime!=null ? date_format($regTime,'Y-m-d H:i:s') : null;						
			//$dateTimeZoneGmt=$quote->getRegularMarketTime()->getTimezone()->getOffset($quote->getRegularMarketTime());
			$line=["marketBeat"=>$marketBeat,
					"assetId"=>$quote->getSymbol(),
					"buyQuote"=>$quote->getRegularMarketPrice(),
					"sellQuote"=>$quote->getRegularMarketPrice(),
					"reportedDate"=>$reportedDate,
					"pollTime"=>$time,
					"pollDate"=>$pollDate];
			
			//print_r($line);


			if ($lastQuote!=null && $lastQuote["reportedDate"]==$reportedDate) {
				//printf("REPEATED!: %s:%s==%s",$quote->getSymbol(),$lastQuote["reportedDate"],$reportedDate);
			} else {
				++$newLines;
				$dsMarketQuotes->addRow($line);	

				$this->pollQuoteAdd($line);	

				if (!$lastQuotesClean) {
					$lastQuotesClean=true;
					$this->marketLastQuotesClean();	
				} 

				$this->pollLastQuoteAdd($line);					
			}			
		}

		if ($newLines>0) {
			Nano\msg(sprintf("YahooFinanceMarketPoll: pollerName:%s poll-completed: quotes:%s",$this->pollerName, $newLines ));
		} else {
			Nano\msg(sprintf("YahooFinanceMarketPoll: poll-repeated: poll source not ready"));
		}
	//Nano\msg(sprintf("YahooFinanceMarketPoll: detail"));		
		//Nano\msg(sprintf("YahooFinanceMarketPoll: ************"));
		//echo $this->marketQuotesAsCsv();
		//Nano\msg(sprintf("YahooFinanceMarketPoll: ************"));
	}
}

?>