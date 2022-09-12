<?php
namespace xnan\MarketPollWeb;

/*
    
*/

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