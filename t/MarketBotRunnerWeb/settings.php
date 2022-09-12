<?php
namespace xnan\Trurl\Horus\MarketBotRunner;

class Functions { const Load=1; }

function testerRefreshMillis() {
  return 2*60*1000;
}

function showPerformance() {
  return false;
}

function cronEnabled() {
    return true;
}

function dndPollSeconds() {
  return 60;
}

function serviceInProd() {
  return false;
}

function runFromCronSetup() {
  $_GET["beatSleep"]="0";
  $_GET["beats"]="1";
  $_GET["q"]="run";
  //$_GET["botArenaId"]="cryptosLiveArena";
}


/*

 *** Tareas de Cron:  

  /usr/local/bin/php /home/xnanclic/public_html/prod/trurl/p0/MarketPollWeb/runFromCron.php >> /home/xnanclic/public_html/prod/trurl/p0/MarketPollWebPollQuotes.log 2>&1

  /usr/local/bin/php /home/xnanclic/public_html/prod/trurl/p0/MarketBotRunnerWeb/runFromCron.php >> /home/xnanclic/public_html/prod/trurl/p0/MarketBotRunnerRun.log 2>&1

  /usr/local/bin/php /home/xnanclic/public_html/prod/trurl/p1/MarketPollWeb/runFromCron.php >> /home/xnanclic/public_html/prod/trurl/p1/MarketPollWebPollQuotes.log 2>&1

  /usr/local/bin/php /home/xnanclic/public_html/prod/trurl/p1/MarketBotRunnerWeb/runFromCron.php >> /home/xnanclic/public_html/prod/trurl/p1/MarketBotRunnerRun.log 2>&1

*/
  
?>