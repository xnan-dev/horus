<?php
namespace xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\BotArena;

require("autoloader.php");
Trurl\Functions::Load;
BotArena\Functions::Load;

BotArena\run();

?>