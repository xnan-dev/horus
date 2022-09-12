<?php

namespace BotArenaWeb\View;

class cViewRefresh extends cView {
	function render($args=[]) {
		$cViewToRefresh=$this->param(array(),"cViewToRefresh");
		$cViewToRefresh=str_ireplace(":","\\",$cViewToRefresh);
		$action=$this->param(array(),"action");
		$action=str_ireplace(":","\\",$action);
		if (strlen($action)>0) $action();
		//$cViewToRefresh();
		$clazz=$cViewToRefresh;
		$obj=new $clazz();
		$obj->render();		

	}
}

?>