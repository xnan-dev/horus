<?php
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

class Loading extends View\cView {
	function render($args=[])  {
		?>
		&nbsp; &nbsp; <span  id="loading" class="spinner-border spinner-border-sm d-none" role="status">
		  <span class="visually-hidden">Loading...</span>
		</span>
		<?php
	}
}
class MarketSignalTabs extends View\cAccordion {
	function renderTab($index) {
		if ($index==0) {	
		    (new PanelBotSuggestions())->render();
		    (new TraderPortfolio())->render();			
		} 
		if ($index==1) (new MarketLastQuotes())->render();    
		if ($index==2) echo "Desactivado"; //(new PlotMarketHistory())->render();
		if ($index==3) (new TraderQueuePending())->render();
		if ($index==4) (new TraderQueueCancelled())->render();
		if ($index==5) (new TraderQueueDone())->render();
		if ($index==6) (new TraderStats())->render();
		if ($index==6) (new TraderMediumStats())->render();
		if ($index==7) (new TraderSettings())->render();		
		if ($index==8) {
			(new MarketSettings())->render();
			(new MarketSchedule())->render();			
		}		
	}
		
	function tabs() {
		 return ["tab0"=>"Señales","tab1"=>"Cotizaciones","tab2"=>"Gráficos","tab3"=>"Ordenes Pendientes","tab4"=>"Ordenes Canceladas","tab5"=>"Historial de operaciones","tab6"=>"Estadísticas del Bot","tab7"=>"Preferencias del Bot","tab8"=>"Preferencias de Mercado"];
	}
}
class PanelMarketSignals  extends View\cView {
	function render($args=[]) {	    
		$botControls=(new PanelBotControls())->renderRet();
		$cBreadCrumb=(new View\cBreadCrumb())->renderRet(array("title"=>"","links"=>array()));		
		$loading=(new Loading())->renderRet();
		$content="&nbsp;".$cBreadCrumb.marketTitle()."&nbsp;".$botControls." ".$loading;
		
		(new View\cTitleBar())->render(array("title"=>$content,"divClass"=>"primary"));		
		
	    (new MarketSignalTabs())->render();
	    	    
	}

	function renderLoading() {
		?>

			

		<?php
	}
}


?>