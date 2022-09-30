<?php
namespace BotArenaWeb;
use BotArenaWeb\View;
require_once("autoloader.php");

abstract class TraderTableView extends View\cTableResponsive {
	var $rows;

	function render($args=[]) {		
		$serviceUrlFn=$this->serviceUrlFn();
		$rows = callServiceCsv($serviceUrlFn(botArenaId(),traderId()),true,defaultShortCacheTtl());
		$this->rows=$rows;
		foreach($this->rows as $i=>&$row) {
			$rows[$i]["title"]=$this->title($row);
		}

		if ($this->countDataRows($rows)==0)  {
			(new View\cInfo())->render(["title"=>$this->emptyMsg()]);
		} else {
			//print marketLastQuotesUrl($botArenaId);
			//printf("<br>head:%s<br>",print_r(array_keys($rows[0]),true));
			//echo print_r($rows[1]);
			$table=array(
				"id"=>$this->tableId(),
				"head"=>$this->head(),
				"rows"=>$rows,
				"titleColumn"=>$this->titleColumn(),				
				"editableFields"=>$this->editableFields(),
				"hiddenColumns"=>$this->hiddenColumns(),
				"data-cView"=>$this->viewClazz(),"data-cViewRefreshMillis"=>"<?php echo defaultViewRefreshMillis(); ?>",
				"data-viewRefreshQuery"=>sprintf("botArenaId=%s&traderId=%s",botArenaId(),traderId()));

			if ($this->filterFn()!="") filterTableRows($table,$this->filterFn());
			
			//(new View\cTableResponsive())->render($table);				
			parent::render($table);
		}		 
	}

	function title(&$row) {
    	return null;
	}


	function titleColumn() {
		return array_keys($this->rows[0])[0];
	}

	function countDataRows(&$rows) {
		return count($rows);
	}
	function editableFields() {
		return [];
	}
	function hiddenColumns() { return array(); }
	abstract function serviceUrlFn();
	function serviceUrlEditFn() { return sprintf("%s%s",serviceUrlFn(),"&format=text"); }
	abstract function viewClazz();
	abstract function tableId();
	abstract function emptyMsg();
	function head() { return null; }
	abstract function filterFn();
}


class TraderPortfolio extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderPortfolioUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderPortfolio"; }	
	function tableId() { return "traderPorfolio"; }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay porfolios asociados"; }
	function hiddenColumns() { return explode(",","traderId,lastDepositQuantity,lastDepositTime"); }
	function editableFields() { return ["assetQuantity"]; }
	function titleColumn() { return "assetId"; }

}

class MarketLastQuotes extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\marketLastQuotesUrl"; }	
	function viewClazz() { return "BotArenaWeb:MarketLastQuotes"; }	
	function tableId() { return "marketLastQuotes"; }
	function titleColumn() { return "assetId"; }	
	//function head() { return ["assetId","buyQuote"]; }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay cotizaciones"; }
}

class TraderQueuePending extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderQueuePendingUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderQueuePending"; }	
	function tableId() { return "traderQueuePending"; }
	///function head() { return explode(";","traderId;queueId;assetId;tradeOp;quantity;limitQuote;currentQuote;status;doable;statusChangeBeat;statusChangeTime"); }
	function filterFn() { return ""; /* "BotArenaWeb\\marketQuotesRowFilter"; */ }
	function emptyMsg() { return "No hay operaciones pendientes"; }
	function hiddenColumns() { return array("traderId"); }
	function titleColumn() { return "assetId"; }
	function title(&$row) { return $this->translate($row["tradeOp"])." ".$row["assetId"]; }

}

class TraderQueueCancelled extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderQueueCancelledUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderQueueCancelled"; }	
	function tableId() { return "traderQueueCancelled"; }
	//function head() { return explode(";","traderId;queueId;assetId;tradeOp;quantity;quote;status;doable;statusChangeBeat;statusChangeTime"); }
	function filterFn() { return ""; /* "BotArenaWeb\\marketQuotesRowFilter"; */}
	function emptyMsg() { return "No hay operaciones canceladas"; }
	function hiddenColumns() { return array("traderId","doable"); }
	function titleColumn() { return "assetId"; }
	function title(&$row) { return $this->translate($row["tradeOp"])." ".$row["assetId"]; }
}

class TraderQueueDone extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderQueueDoneUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderQueueDone"; }	
	function tableId() { return "traderQueueDone"; }
	//function head() { return explode(";","traderId;queueId;assetId;tradeOp;quantity;limitQuote;doneQuote;doneBalance;valuation;status;doable;statusChangeBeat;statusChangeTime;doneBeat;doneTime"); }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay operaciones realizadas"; }
	function hiddenColumns() { return array("traderId","doable","status"); }
	function titleColumn() { return "assetId"; }
	function title(&$row) { return $this->translate($row["tradeOp"])." ".$row["assetId"]; }
}

class TraderSettings extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderSettingsUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderSettings"; }	
	function tableId() { return "traderSettings"; }
	//function head() { return explode(";","settingsKey;settingsDescription;settingsValue"); }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay preferencias definidas"; }
	function titleColumn() { return "settingsKey"; }	
}

class MarketSettings extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\marketSettingsUrl"; }	
	function viewClazz() { return "BotArenaWeb:MarketSettings"; }	
	function tableId() { return "marketSettings"; }
	//function head() { return explode(";","settingsKey;settingsDescription;settingsValue"); }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay preferencias definidas"; }
	function titleColumn() { return "settingsKey"; }

}

class MarketSchedule extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\marketScheduleUrl"; }	
	function viewClazz() { return "BotArenaWeb:MarketScheduleSettings"; }	
	function tableId() { return "marketScheduleSettings"; }
	//function head() { return explode(";","settingsKey;settingsDescription;settingsValue"); }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay preferencias de cronograma definidas"; }
	function titleColumn() { return "settingsKey"; }

}


class TraderStats extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderStatsUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderStats"; }	
	function tableId() { return "traderStats"; }
	//function head() { return ["assetId","value","mean","max","min","cicle","maxBuyByStrategy"]; }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay estadísticas disponibles"; }
	function titleColumn() { return "assetId"; }

}


class TraderMediumStats extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderMediumStatsUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderMediumStats"; }	
	function tableId() { return "traderMediumStats"; }
	//function head() { return ["assetId","value","mean","max","min","cicle","maxBuyByStrategy"]; }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay estadísticas disponibles"; }
	function titleColumn() { return "assetId"; }
}

class TraderLongStats extends TraderTableView {
	function serviceUrlFn() { return "BotArenaWeb\\traderLongStatsUrl"; }	
	function viewClazz() { return "BotArenaWeb:TraderLongStats"; }	
	function tableId() { return "traderLongStats"; }
	//function head() { return ["assetId","value","mean","max","min","cicle","maxBuyByStrategy"]; }
	function filterFn() { return ""; /*"BotArenaWeb\\marketQuotesRowFilter";*/ }
	function emptyMsg() { return "No hay estadísticas disponibles"; }
	function titleColumn() { return "assetId"; }
}

?>