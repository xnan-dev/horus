<?php
namespace BotArenaWeb\View;

class cAcceptCancelButton extends cView {

	function render($args=array("title"=>"a button","subtitle"=>1,"btn-class"=>"btn-primary","acceptAction"=>"opAccept","cancelAction"=>"opCancel")) { ?>
	
	<div class="btn-group" role="group" aria-label="Button group with nested">
	  <button type="button" class="btn <?php $this->paramWrite($args,"btn-class");?>"><h6><?php $this->paramWrite($args,"title");?></h6></button>
	  <button type="button" class="btn btn-dark" <?php $this->dataAttrWrite($args);?> data-action="<?php $this->paramWrite($args,"acceptAction");?>"><h6><?php iconWrite2("bag-check")?>&nbsp Aceptar&nbsp;</h6></button>
	  <button type="button" class="btn btn-secondary" <?php $this->dataAttrWrite($args);?> data-action="<?php $this->paramWrite($args,"cancelAction");?>"><h6><?php iconWrite2("bag-x")?>&nbsp Cancelar&nbsp;</h6></button>
	</div>
	<?php
	}
}

?>
