<?php
namespace BotArenaWeb;

/*
  * parametros de query
    
    restart=true : elimina la sesión activa.
    performance=true : muestra la performance al cargar index.
    cache=false : desactiva el uso de cache de servicios.
*/

function botArenaWebVersion() {
  return "1.2.1 T/local";
}

function botArenaUrl() {  
  return sprintf("%s/BotArenaWeb/index.php",domain());
}

function marketPollWebUrl() {  
  return sprintf("%s/MarketPollWeb",domain());
}

function marketBotRunnerWebUrl() {
  return sprintf("%s/MarketBotRunnerWeb/index.php",domain());  
}

function domain() {
  $protocol=$_SERVER["REQUEST_SCHEME"]; 
  $domain=$_SERVER["SERVER_NAME"];
  if ($domain=="") return domainWhenUndetected();
  $domain="$protocol://".$domain;
  return $domain;
}

function domainWhenUndetected() {
 return "http://local.t.trurl.xnan.click";
}

function defaultShortCacheTtl() {
  return 10;
}

function defaultLongCacheTtl() {
  return 60*5;
}

function defaultViewRefreshMillis() {
    return 500;
}

function jsViewRefreshMillis() {
  return 10000;
}

function sessionCacheExpireSeconds() {
  return 24*60*60; 
}

function testerRefreshMillis() {
  return 2*60*1000;
}

function minDiskAvailable() {
  return 70*1000*1000;
}

?>