<?php
namespace xnan\Mikro\Test;

use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\MarketSimulator;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\MarketPoll;
use xnan\Trurl\Horus\BotArena;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Nano\TextFormatter;
use xnan\Trurl\Horus\Builders;
use xnan\Trurl\Horus\Cube;

chdir( __DIR__ );

require("autoloader.php");

include_once("common.php");
include_once("settings.php");

Trurl\Functions::Load;
BotArena\Functions::Load;
BotWorld\Functions::Load;
Builders\Functions::Load;
Horus\Functions::Load;

class SanityTest {

	function botArenaIds() {
		$rows=Trurl\callServiceMarketBotRunnerArray("q=botArenasAsCsv&live=true");
		$r=[];
		foreach($rows as $row) {
			$r[]=$row["botArenaId"];
		}
		return $r;
	}

	function traderIds() {
		$rows=Trurl\callServiceMarketBotRunnerArray("http://local.t.trurl.xnan.click/MarketBotRunnerWeb/index.php?q=botArenaTradersAsCsv&botArenaId=mathArena");
		print_r($rows);
		$r=[];
		foreach($rows as $row) {
			$r[]=$row["traderId"];
		}
		return $r;
	}


	function parameterOptions($ps,$params,$pi) {
		$p=$ps[$pi];
		
		if ($p=="traderId") {
			$ret=$this->traderIds();
		}
		return $ret;
	}

	function replaceParameters($query,$ps,$pi) {
		$values=["botArenaId"=>$this->botArenaIds(),"traderId"=>["aaa"]];
		$qis=[];
		$p=$ps[$pi];
		print_r($this->parameterOptions($ps,["botArena"=>"mathLiveArena"],$pi));
		exit();
		if (strpos($query,"{".$p."}")===FALSE) return [];
		
		foreach($values[$p] as $value) {
			$queryInstance=$query;
			$queryInstance=str_replace("{".$p."}",$value,$queryInstance);			
			//print "QI:$queryInstance , value:$value<br>";
			$qr=[$queryInstance,[$value]];

			if ($pi<count($ps)-1) {
				$qras=$this->replaceParameters($qr[0],$ps,$pi+1);
				foreach($qras as $qra) {
					$qis[]=$qra;	
				}
				//print_r($qra);
			} else {			
				$qra=$qr;
				$qis[]=$qra;
			}		
		}		

		return $qis;
		
	}

	function queryParameters($query) {
		preg_match_all('/{([A-Za-z]+)}/', $query, $match);
		$r=[];
		if (count($match)>=1) {
			for($mi=0;$mi<count($match[1]);$mi++) {
				$r[]=$match[1][$mi];
			}			
		} 		
		return $r;
	}

	function testService() {	
		$queries=$runner->serviceQueries();		

		$this->replaceParameters("q=botSuggestionsAsCsv&botArenaId=mathLiveArena&traderId={traderId}",["traderId"],0);
		exit();

		$runner=MarketBotRunner::instance();


		foreach ($queries as $name=>$q) {				
			$query=$q->query();
			$ps=queryParameters($query);
			//print_r($ps);
			if (count($ps)>0) {
				$queryInstances=$this->replaceParameters($query,$ps,0);				
				/*print "<pre>";
				print_r($queryInstances);
				print "</pre>";*/
			} else {
				$queryInstances=[ [$query,[]] ];
			}

			foreach($queryInstances as $queryPair) {
				try {
					//print "QUERY PAIR:";
					//print_r($queryPair);print "<br><br>";

					$queryInstance=$queryPair[0];
					$queryParams=$queryPair[1];				
					$queryParamsStr=implode(",",$queryParams);

					$link=my_link($name,Trurl\marketBotRunnerQuery($queryInstance));
					$timeoutFn=$q->timeoutFn();				
					$rows=Trurl\callServiceMarketBotRunnerArray($queryInstance,$timeoutFn);

					if (count($rows)>0 && count(array_keys($rows[0]))<=1) {
						printf("TEST <b>FAIL</b> serviceQuery query:%s params:%s msg:%s<br>\n",$link,$queryParamsStr,"too few columns in csv");
					} else {
						printf("TEST <b>OK</b> serviceQuery %s params:%s<br>\n",$link,$queryParamsStr);					
					}							
				} catch(\Exception $e) {
					printf("TEST <b>FAIL</b> serviceQuery %s params:%s msg:%s<br>\n",$link,$queryParamsStr,$e->getMessage());				
					exit();
				}
			}
		}

	}
}

(new SanityTest())->testService();

?>