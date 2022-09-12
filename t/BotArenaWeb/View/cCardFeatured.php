<?php 
namespace BotArenaWeb\View;

class cCardFeatured extends cView {
	function render($args=array("title"=>"","badgeType"=>"warning","headingLevel"=>3)) { ?>
	<div class="card text-center">
  		<div class="card-header">
    		<?php $this->renderHeader($args);?>
  		</div> 	   
  		<div class="card-body">
		    <h5 class="card-title"><?php $this->renderTitle($args);?></h5>
		    <p class="card-text"><?php $this->renderContent($args);?></p>		    
		  </div>
		  <div class="card-footer text-muted">
		    <p class="card-text"><?php $this->renderFooter($args);?></p>
		  </div>
		</div>
	</div>
	<?php 	
	} 

	function renderHeader($args) {
		$this->paramWrite($args,"header");
	}

	function renderTitle($args) {
		$this->paramWrite($args,"title");
	}

	function renderContent($args) {
		$this->paramWrite($args,"content");
	}

	function renderFooter($args) {
		$this->paramWrite($args,"footer");
	}
}
?>