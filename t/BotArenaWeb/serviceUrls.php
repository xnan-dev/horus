<?php

namespace BotArenaWeb;
use BotArenaWeb\View;

include_once("settings.php");


function botArenasUrl($live) {
	$liveStr=$live ? "true" : "false";
	return sprintf('%s?q=botArenasAsCsv&live=%s',MarketBotRunnerWebUrl(),$liveStr);
}

function botArenaTradersUrl($botArenaId) {
	return sprintf("%s?q=botArenaTradersAsCsv&botArenaId=$botArenaId",MarketBotRunnerWebUrl());
}

function marketQuotesUrl($botArenaId="") {
	return sprintf('%s?q=marketQuotes&botArenaId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId);
}

function marketLastQuotesUrl($botArenaId="") {
	return sprintf('%s?q=marketLastQuotesAsCsv&botArenaId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId);
}

function marketStatusUrl($botArenaId="") {
	return sprintf('%s/content/poll/SimMarket/marketStatus.csv',MarketBotRunnerWebUrl());
}

function traderStatusUrl($botArenaId="") {
	return sprintf('%s/content/poll/SimMarket/traderStatus.csv',MarketBotRunnerWebUrl());
}

function traderPortfolioUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderPortfolioAsCsv&botArenaId=%s&traderId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId,$traderId);	
	return $url;
}

function botSuggestionsUrl($botArenaId,$traderId="",$textFormat="html") {
	$url=sprintf('%s?q=botSuggestionsAsCsv&botArenaId=%s&traderId=%s&textFormat=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId,$textFormat);
	return $url;
}

function traderQueueCancelledUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderQueueCancelledAsCsv&botArenaId=%s&traderId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function traderSettingsUrl($botArenaId,$traderId="",$textFormat="html") {
	$url=sprintf('%s?q=traderSettingsAsCsv&botArenaId=%s&traderId=%s&textFormat=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId,$textFormat);
	return $url;
}

function traderStatsUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderStatsAsCsv&botArenaId=%s&traderId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function traderMediumStatsUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderStatsMediumAsCsv&botArenaId=%s&traderId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function traderLongStatsUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderStatsLongAsCsv&botArenaId=%s&traderId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function marketSettingsUrl($botArenaId,$textFormat="html") {
	$url=sprintf('%s?q=marketSettingsAsCsv&botArenaId=%s&textFormat=%s',MarketBotRunnerWebUrl(),$botArenaId,$textFormat);
	return $url;
}

function marketScheduleUrl($botArenaId) {
	$url=sprintf('%s?q=marketScheduleAsCsv&botArenaId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId);
	return $url;
}

function traderQueuePendingUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderQueuePendingAsCsv&botArenaId=%s&traderId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function traderQueueDoneUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderQueueDoneAsCsv&botArenaId=%s&traderId=%s&textFormat=html',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function traderSuspendUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderSuspend&botArenaId=%s&traderId=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function traderResumeUrl($botArenaId,$traderId="") {
	$url=sprintf('%s?q=traderResume&botArenaId=%s&traderId=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
	return $url;
}

function tradeOpAcceptUrl($botArenaId,$traderId="",$queueId="-1") {
	$url=sprintf('%s?q=tradeOpAccept&botArenaId=%s&traderId=%s&queueId=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId,$queueId);
	return $url;
}

function tradeOpCancelUrl($botArenaId,$traderId="",$queueId="-1") {
	$url=sprintf('%s?q=tradeOpCancel&botArenaId=%s&traderId=%s&queueId=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId,$queueId);
	debugWrite("botSuggestionsUrl: $url");
	return $url;
}

function marketHistoryAsJsonUrl($botArenaId,$traderId="") {
	return sprintf('%s?q=marketHistoryAsJson&xcount=50&botArenaId=%s&traderId=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
}

function traderHistoryAsJsonUrl($botArenaId,$traderId="") {
	return sprintf('%s?q=traderHistoryAsJson&xcount=50&botArenaId=%s&traderId=%s',MarketBotRunnerWebUrl(),$botArenaId,$traderId);
}


function findSettingsByKey($rows,$key) {  
	if (!is_array($rows)) {
		exit("findSettingsByKey: key:$key msg: rows should be array");
	}
  foreach($rows as $row) {
    if ($row["settingsKey"]==$key) return $row;
  }
  return null;
}

function botArenas($live) {
  return callServiceCsv(botArenasUrl($live),true,defaultLongCacheTtl());
}

function botArenaTraders($botArenaId) {
  return callServiceCsv(botArenaTradersUrl($botArenaId,true,defaultLongCacheTtl()));
}

function traderSettings($key) {
  $traderSettingsRows=callServiceCsv(traderSettingsUrl(botArenaId(),traderId(),"text"),true,defaultShortCacheTtl());
  $row=findSettingsByKey($traderSettingsRows,$key);
  return $row["settingsValue"];
}

function traderAutoApprove() {
	return traderSettings("autoApprove")=="true";
}

function marketSettings($key) {	
  $marketSettingsRows=callServiceCsv(marketSettingsUrl(botArenaId(),"text"),true,defaultShortCacheTtl());  
  $row=findSettingsByKey($marketSettingsRows,$key);
  return $row["settingsValue"];
}

function marketSchedule($key) {	
  $marketSettingsRows=callServiceCsv(marketScheduleUrl(botArenaId(),"text"),true,defaultShortCacheTtl());  
  $row=findSettingsByKey($marketSettingsRows,$key);
  return $row["settingsValue"];
}

function marketScheduleIsOpen() {  
  return marketSchedule("marketIsOpen");
}

function marketScheduleTodayIsOpen() {  
  return marketSchedule("marketIsTodayOpen")=="1";
}

function marketScheduleIsClosed() {  
  return !marketScheduleIsOpen();
}

function marketScheduleTodayIsClosed() {  
  return !marketScheduleTodayIsOpen();
}

function marketId() {
  return marketSettings("marketId");
}

function marketUseHistory() {
  return marketSettings("useHistory")=="true";  
}

function marketTitle() {
  return marketSettings("marketTitle");
}

function marketFinalBeat() {
  return marketSettings("finalBeat");
}

function marketBeat() {
  return marketSettings("beat");
}

function marketPollContentOutdated() {
  return marketSettings("pollContentOutdated")=="true";
}


function traderId() {
  return (new View\cView())->param(array(),"traderId","choose");
  //return traderSettings("traderId");
}

function traderTitle() {
  return traderSettings("traderTitle");
}

function botArenaId() {
  return (new View\cView())->param(array(),"botArenaId","mathArena");
}

function traderIsSuspended() {
    return traderSettings("isSuspended")=="true";    
}

function isDebug() {
  return ((new View\cView())->param(array(),"debug","false"))=="true";
}


function checkDiskAvailable() {
	$free=\disk_free_space(".");
	if ($free<minDiskAvailable()) {
		checkFailed(sprintf("not enough disk space (%s < %s bytes)",$free,minDiskAvailable()));
	}
}
?>