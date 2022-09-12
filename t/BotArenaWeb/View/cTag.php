<?php 
namespace BotArenaWeb\View;

class cTag extends cView {
	function render($args=array("title"=>"","badgeType"=>"warning","headingLevel"=>3)) { ?>		  
		<h<?php $this->paramWrite($args,"headingLevel");?> class="d-inline-block">
			<span class="badge bg-<?php $this->paramWrite($args,"badgeType");?>"><?php $this->paramWrite($args,"title");?>
				
			</span>
		</h<?php $this->paramWrite($args,"headingLevel");?>>
	<?php 
	} 
}
?>