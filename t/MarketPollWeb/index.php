<?php
namespace xnan\MarketPollWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\Market;
use xnan\Trurl\Horus\MarketSimulator;
use xnan\Trurl\Horus\Asset;
use xnan\Trurl\Horus\MarketPoll;
use xnan\Trurl\Horus\BotArena;
use xnan\Trurl\Horus\MarketStats;
use xnan\Trurl\Horus\BotWorld;

chdir( __DIR__ );

require("autoloader.php");
require '../vendor/autoload.php';

Trurl\Functions::Load;
Trurl\Functions::Load;
BotArena\Functions::Load;
BotWorld\Functions::Load;

include_once("common.php");
