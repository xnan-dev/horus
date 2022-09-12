<?php 
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

class cMarkets extends View\cView {
	function render($args=[]) {
		$live=$args["live"];
		$botArenas=botArenas($live);
		$botArenaId=botArenaId();
		?>
		<div class="container">
		 <div class="row"> <?php
		foreach($botArenas as $botArena) {
			?> <div class="col-sm-3"> <?php
  			
			(new View\cVisitPanel())->render(
				array(
					"title"=>$botArena["marketTitle"],
					"description"=>$botArena["marketTitle"],
					"thumbSrc"=>sprintf("content/logos/%s.png",$botArena["botArenaId"]),
					"actionTitle"=>"Visitar",
					"actionUrl"=>sprintf("index.php?slotMain=PanelMarketActivityPicker&botArenaId=%s",
						$botArena["botArenaId"])
					)
				);
			?> </div> <?php
		}
		?> </div></div>

		<?php 		
	}
}
class PanelMarketPicker extends View\cView {
	function render($args=[]) {		
		//<?php 
		$cBreadCrumb=(new View\cBreadCrumb())->renderRet(array("title"=>"","links"=>array()));
		$version=botArenaWebVersion();
		$title="$cBreadCrumb v$version &nbsp;&nbsp;&nbsp;Mercados";

		(new View\cTitleBar())->render(array("title"=>$title,"divClass"=>"primary"));

		(new View\cTitleBar())->render(array("title"=>"En vivo","divClass"=>"secondary"));
		(new cMarkets())->render(["live"=>true]);

		(new View\cTitleBar())->render(array("title"=>"Zona de Pruebas","divClass"=>"secondary"));
		(new cMarkets())->render(["live"=>false]);
	}
}

?>
