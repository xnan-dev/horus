<?php
namespace BotArenaWeb;

function filterRows($rows,$filterFn) {
	$outRows=array();
	foreach($rows as $row) {
		$include=$filterFn($row);
		if ($include) $outRows[]=$row;
	}
	return $outRows;
}

function filterHead($head,$filterFn) {
	$outHead=array();
	foreach($head as $cell) {
		$include=$filterFn($cell);
		if ($include) $outHead[]=$cell;
	}
	return $outHead;
}

function filterTableRows(&$table,$filterFn) {
	$table["rows"]=filterRows($table["rows"],$filterFn);						
}


function marketStatusFilterHead(&$cell) {
	return $cell!="USD";
}


function marketStatusRowFilter(&$row) {
	$keys=array_keys($row);
	for($i=1;$i<count($row);$i++) {
		$row[$keys[$i]]=number_format($row[$keys[$i]],2);	
	}
	unset($row["USD"]);
	return true;
}

function colsAllowed(&$row,$cols) {
	$keys=array_keys($row);
	//print "keys:";
	//print_r($keys);
	//print "<br>";

	foreach($row as $key=>$value) {		
		if (!in_array($key,$cols)) {
			//print "unset: $key<br>";
			unset($row[$key]);
		}
	}	

	//print "row-allowed:";
	//print_r($row);
	//print "<br>";
}

function marketQuotesRowFilter(&$row) {
	colsAllowed($row,["assetId","buyQuote"]);	
	$row["buyQuote"]=number_format($row["buyQuote"],2);		
	
	return ($row["assetId"]!="USD");
}

function traderPortfolioRowFilter(&$row) {
	$keys=array_keys($row);
	for($i=1;$i<count($row);$i++) {
		$value=$row[$keys[$i]];
		if (is_numeric($value)) {
			$row[$keys[$i]]=number_format($value,2);		
		}		
	}	
	//return ($row["assetId"]!="USD");
	return true;
}

function traderStatusRowFilter(&$row) {
	$keys=array_keys($row);
	for($i=1;$i<count($row);$i++) {
		$row[$keys[$i]]=number_format($row[$keys[$i]],2);	
	}
	return true;
}

?>