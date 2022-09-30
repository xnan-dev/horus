<?php

namespace xnan\Trurl\Horus;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

use xnan\Trurl\Horus\Persistence;

include_once("settings.php");	
require("autoloader.php");	

class Functions {const Load=1;}

function marketBotRunnerQuery($extras) {
	return sprintf("%s?%s",marketBotRunnerWebUrl(),$extras);
}

function callServiceMarketBotRunnerCsv($query,$timeoutFn=null) {
	return Nano\nanoFile()->fileGetContents(marketBotRunnerQuery($query),$timeoutFn);	
}

function callServiceMarketBotRunnerArray($query,$timeoutFn=null) {
	return Nano\nanoCsv()->csvContentToArray(callServiceMarketBotRunnerCsv($query,$timeoutFn),';');
}

function marketPollerQuery($query,$pollerName) {
  if ($pollerName=="") Nano\nanoCheck()->checkFailed("pollerQuery: pollerName: msg: cannot be empty");
  $url=sprintf("%s/index.php?pollerName=%s&%s",marketPollWebUrl(),$pollerName,$query); 
  return $url;
}

function callServiceMarketPollerCsv($query,$pollerName,$timeoutFn=null) {	
	return Nano\nanoFile()->fileGetContents(marketPollerQuery($query,$pollerName),$timeoutFn);	
}

function callServiceMarketPollerArray($query,$pollerName,$timeoutFn=null) {
	$url=marketPollerQuery($query,$pollerName);			
	$arr=Nano\nanoCsv()->csvContentToArray(Nano\nanoFile()->fileGetContents($url,$timeoutFn),';');	
	if (!is_array($arr)) throw new \Exception("query: $query pollerName:$pollerName msg: csv content is not an array");
	return $arr;
}

function marketBotRunnerVersion() {
  return "2.1.0/local";
}

function persistence() {
		return Persistence\Persistence::instance();
}

?>