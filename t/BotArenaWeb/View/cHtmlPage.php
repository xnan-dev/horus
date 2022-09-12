<?php
namespace BotArenaWeb\View;
class cHtmlPage extends cView {

function render($args=array()) { ?>
	<!DOCTYPE html>
	<html lang="en">
	 <?php (new cHead())->render(array()); ?>
	 <body> 
	 <?php (new cBodyScripts())->render(array()); ?>

	    <div class="container-fluid">
			<div class="row">
				<div class="col-md-12">			
					<?php //cTitleWrite(array("title"=>"Carozo Capitals","subtitle"=>"Asistencia estratégica para operar mercados de capitales")); ?>

					<?php //cInfoWrite(array("title"=>"El sistema está fuera de servicio","div-class"=>"alert-primary"));?>

					<?php (new \BotArenaWeb\SlotMain())->render(); ?>

				</div>
			</div>	
		</div>














		<?php //(new cBodyScripts())->render()?>
	  </body>
	</html>
	<?php } 
}
?>
