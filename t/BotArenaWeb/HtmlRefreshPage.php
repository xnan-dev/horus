<?php
namespace xnan\BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;

class HtmlRefreshPage {	
	function render() {
		$cViewToRefresh=param(array(),"cViewToRefresh","");
		$clazz="BotArenaWeb\\$cViewToRefresh";
		$obj=new $clazz;
		$obj->render();
	}
}

?>