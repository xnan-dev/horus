<?php

namespace xnan\Trurl\Horus;

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

function botArenaUrl() {  
  return sprintf("%s/BotArenaWeb/index.php",domain());
}

function marketPollWebUrl() {  
  return sprintf("%s/MarketPollWeb",domain());
}
  
function marketBotRunnerWebUrl() {
  return sprintf("%s/MarketBotRunnerWeb/index.php",domain());  
}

?>