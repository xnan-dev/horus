<?php 

namespace BotArenaWeb\View;

class cDispatchView extends cView {
	function render($args=[]) {
		$cView=$this->param(array(),"cView");
		if (strlen($cView)>0) {
			$slotMain=$this->param(array(),"slotMain","Home");
			$clazz="\\BotArenaWeb\\$cView";
			$obj=new $clazz();
			$obj->render();		
		}
	}	
}
?>