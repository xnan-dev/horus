<?php
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

class SlotMain extends View\cView {	
	function render($args=[]) {
		$slotMain=$this->param(array(),"slotMain","Home");
		$clazz="BotArenaWeb\\$slotMain";
		new Home();
		$obj=new $clazz();
		$obj->render();
	}
}

?>
