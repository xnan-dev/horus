<?php
namespace BotArenaWeb\View;

class cTitle extends cView {
	function args() {
	    $args=array("level"=>3,"title"=>"some title","subtitle"=>"here goes the subtitle");
	}

	 function render($args=[]) { 
	    if ($args==null) $args=cTitleArgs();
	?>
	<h<?php echo $this->param($args,"level",3) ?>>
	<?php echo $this->param($args,"title")?> <small><?php echo $this->param($args,"subtitle")?></small>
	</h<?php echo $this->param($args,"level",3) ?>>
	 <?php 

	}
}

?>