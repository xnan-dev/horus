<?php
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

class Home  extends View\cView {
	function render($args=[]) {
		(new PanelMarketPicker())->render();
	}	
}

?>