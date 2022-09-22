<?php
namespace xnan\MarketBotRunner;

chdir( __DIR__ );

require("autoloader.php");
require '../vendor/autoload.php';

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


$pdoSettings=(new PdoSettings\PdoSettings())
	->withHostname("localhost")
	->withDatabase("horus_t")
	->withUser("root")
	->withPassword("root11");

Nano\nanoLog()->open();

//Hydra\hydra()->hydrate();
(MarketBotRunner\MarketBotRunner::instance())->pdoSettings($pdoSettings);
(MarketBotRunner\MarketBotRunner::instance())->serviceProcess();
//Hydra\hydra()->dehydrate();			
Nano\nanoLog()->close();

?>
