<?php
namespace BotArenaWeb\View;

class cVisitPanel extends cView {
	function render($args=[]) {
	?>

	<div class="bg-light h-75 p-3 m-3 rounded-lg shadow">
	
		<div class="h-75">
			<h5 class="">
			<?php if (array_key_exists("thumbSrc",$args)) { ?>
				<div class="m-2 mb-4">
						<img src="<?php $this->paramWrite($args,"thumbSrc") ?>" class="img-thumbnail" alt="..."/>				
				</div>
			<?php  } ?>

				<?php $this->paramWrite($args,"title");?>
			</h5>
			<p>
				<?php $this->paramWrite($args,"description");?>				
			</p>		
		</div>
  	   
  	   <div class="d-flex flex-row-reverse bd-highlight">
  	   		<div class="p-0 m-0 bd-highlight">
  	   			<a class="btn btn-primary btn-sm" href="<?php $this->paramWrite($args,"actionUrl");?>"><?php $this->paramWrite($args,"actionTitle");?></a>
  			</div>		
		</div>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
	</div>
	<?php
	}	
}
?>