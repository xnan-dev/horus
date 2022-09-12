<?php

namespace BotArenaWeb\View;

class cBadgeButton extends cView {

	function render($args=array("title"=>"a button","subtitle"=>1,"btn-class"=>"btn-primary")) { ?>
	  <li class="nav-item">
	 	<a class="nav-link btn <?php $this->paramWrite($args,"btn-class");?>" href="#" <?php $this->dataAttrWrite($args);?>>
	 		<div class="title"><?php $this->paramWrite($args,"title");?></div> 
	 		<span class="badge badge-light"><?php $this->paramWrite($args,"subtitle");?></span>
	 	</a>
	 	</li>
	<?php
	}
}
?>
