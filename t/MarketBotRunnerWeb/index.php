<?php
namespace xnan\Trurl\Horus\MarketBotRunner;

chdir( __DIR__ );

require("autoloader.php");
require '../vendor/autoload.php';
include_once("settings.php");

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Mycro: Main
use xnan\Trurl\Mikro\ServiceQuery;
use xnan\Trurl\Mikro\RestService;

// Uses:  Horus: Shortcuts
use xnan\Trurl\Horus;
Horus\Functions::Load;

//Uses: Custom
use xnan\Trurl\Horus\MarketBotRunner;
use xnan\Trurl\Horus\PdoSettings;

//Uses: End

error_reporting(E_ALL);

Nano\nanoLog()->open();
(MarketBotRunner\MarketBotRunner::instance())->pdoSettings(pdoSettings());
(MarketBotRunner\MarketBotRunner::instance())->serviceProcess();
if (showPerformance()) Nano\nanoPerformance()->summaryWrite();
Nano\nanoLog()->close();

?>
