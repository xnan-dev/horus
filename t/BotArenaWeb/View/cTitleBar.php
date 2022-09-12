<?php 
namespace BotArenaWeb\View;

class cTitleBar extends cView {
	function render($args=array("title"=>"","div-class"=>"alert-warning")) { ?>
	<h3 class="mt-3">
	<div class="alert alert-<?php $this->paramWrite($args,'divClass');?>" role="alert">
		  <?php $this->paramWrite($args,"title");?>
	</div>
	</h3>
	<?php 
	} 
}
?>