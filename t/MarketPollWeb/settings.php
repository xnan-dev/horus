<?php
namespace xnan\MarketPollWeb;

use xnan\Trurl\Horus\PdoSettings;

class Functions { const Load=1; }

function pdoSettings() {
  return 
    (new PdoSettings\PdoSettings())
    ->withHostname("localhost")
    ->withDatabase("horusMarketPoll_t")
    ->withUser("root")
    ->withPassword("root11");
}

function testerRefreshMillis() {
  return 2*60*1000;
}

function showPerformance() {
  return false;
}

function runFromCronSetup() {
  $_GET["beatSleep"]="0";
  $_GET["beats"]="1";
  $_GET["q"]="pollQuotes";  
}

function cronEnabled() {
    return true;
}

function dndPollSeconds() {
  return 60;
}

?>