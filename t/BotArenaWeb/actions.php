<?php
namespace BotArenaWeb;

function tradeOpAccept() {
  $botArenaId=(new View\cView())->param(array(),"botArenaId");
  $traderId=(new View\cView())->param(array(),"traderId");
  $queueId=(new View\cView())->param(array(),"queueId");
  $url=tradeOpAcceptUrl($botArenaId,$traderId,$queueId);
  my_file_get_contents($url);
  //exit("tradeOpAccept!"); 
  addInfo("Operación $queueId aprobada");
}

function tradeOpCancel() {
  $botArenaId=(new View\cView())->param(array(),"botArenaId");
  $traderId=(new View\cView())->param(array(),"traderId");
  $queueId=(new View\cView())->param(array(),"queueId");
  $url=tradeOpCancelUrl($botArenaId,$traderId,$queueId);
  my_file_get_contents($url);
  //exit("tradeOpAccept!"); 
  addInfo("Operación $queueId cancelada");
}

function traderSuspend() {
  $url=traderSuspendUrl(botArenaId(),traderId());
  callServiceCsv($url);  
}

function traderResume() {
  $url=traderResumeUrl(botArenaId(),traderId());
  callServiceCsv($url);
}

function cellSave() {
  $saveUrl=(new View\cView())->param([],"saveUrl");
  $saveUrl=sprintf('%s?q=worldSettingsChange&%s',MarketBotRunnerWebUrl(),$saveUrl);
  print "$saveUrl<br>\n";
  callServiceCsv($saveUrl);
}

?>