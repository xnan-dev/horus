<?php 
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

Trurl\Functions::Load;

class PanelMarketActivityPicker extends View\cView {
	function renderTraderActivities($traderId,$traderTitle) {
		$botArenaId=botArenaId();
		(new View\cVisitPanel())->render(array("title"=>"Señales $traderTitle","description"=>"Señales para $traderTitle","actionTitle"=>"Visitar","actionUrl"=>"index.php?slotMain=PanelMarketSignals&botArenaId=$botArenaId&traderId=$traderId"));
			}

	function render($args=[]) {
		$cBreadCrumb=(new View\cBreadCrumb())->renderRet(array("title"=>"","links"=>array()));
		
		(new View\cTitleBar())->render(array("title"=>"$cBreadCrumb Actividades","divClass"=>"primary"));

		$traders=botArenaTraders(botArenaId());
		
		?>
		<div class="container">
		 <div class="row"> <?php

		foreach($traders as $trader) {
			?> <div class="col-sm-4"> <?php
			 $this->renderTraderActivities($trader["traderId"],$trader["traderTitle"]);
 			?> </div> <?php
		}		
		?> </div></div> <?php

		//if (botArenaId()=="cryptosLiveArena") $this->renderTraderActivities("botLive","Bot Vivo");

		//if (botArenaId()=="mathArena") $this->renderTraderActivities("botMath","Bot de Prueba");

		//(new View\cVisitPanel())->render(array("title"=>"Simulación","description"=>"Permite probar bots y ver los resultados en una simulación a partir de datos históricos o generados del mercado ","actionTitle"=>"Visitar","actionUrl"=>"index.php?slotMain=PanelMarketSimulation"));
		//(new View\cVisitPanel())->render(array("title"=>"Cartera","description"=>"Para ver y configurar la cartera del cliente","actionTitle"=>"Visitar","actionUrl"=>"index.php?slotMain=panelPortfolio"));
	}
}
?>
