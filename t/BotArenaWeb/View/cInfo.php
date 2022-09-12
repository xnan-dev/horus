<?php 
namespace BotArenaWeb\View;

class cInfo extends cView {
	function render($args=array("title"=>"","div-class"=>"alert-warning")) { ?>
	<div class="alert alert-<?php $this->paramWrite($args,'divClass',"secondary");?>" role="alert">
		  <?php $this->paramWrite($args,"title");?>
	</div>
	<?php 
	} 
}
?>