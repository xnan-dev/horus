<?php
namespace BotArenaWeb;
use xnan\Trurl;
use BotArenaWeb\View;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\MarketSimulator;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\MarketPoll;
use xnan\Trurl\Horus\BotArena;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Nano;


include_once("serviceHelper.php");
include_once("serviceUrls.php");
include_once("tableFilters.php");
include_once("tables.php");
include_once("actions.php");
include_once("infos.php");
include_once("settings.php");
require_once("autoloader.php");
require '../vendor/autoload.php';

Nano\Functions::Load;


error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$performance=Nano\nanoPerformance();
$performance->textFormat("html");

Trurl\Functions::Load;
View\Functions::Load;
AssetTradeOperation\Functions::Load;

 date_default_timezone_set("America/Argentina/Buenos_Aires");
 
 session_cache_limiter('nocache');
 session_cache_expire(sessionCacheExpireSeconds());
 session_start();

if ((new View\cView())->param(array(),"restart","false")=="true") {
	session_regenerate_id(true);
	print "session_id:".session_id();
	session_destroy();
	exit();
}

 $cView=(new View\cView())->param(array(),"cView","View\\cHtmlPage");

if ($cView=="View\\cHtmlPage") {
	(new View\cHtmlPage())->render();	
} else if ($cView=="View\\cViewRefresh") {
	(new View\cViewRefresh())->render();		
}

if ((new View\cView())->param(array(),"performance","false")=="true") $performance->summaryWrite();


//(new View\cDispatchView())->render();


 ?>