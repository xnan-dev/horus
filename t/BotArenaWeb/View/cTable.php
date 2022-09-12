<?php
namespace BotArenaWeb\View;

class cTable extends cView {


	function cTableArgs() {
		return array(
		"head"=>explode(",","A,B,C,D"),
		"rows"=>array(
				explode(",","A,B,C,D"),
				explode(",","E,F,G,H")
			)
		);
	}

	function render($args=[]) {
		if ($args==null) $args=cTableArgs();		
		$hiddenColumns=$this->param($args,"hiddenColumns",[]);
	?>
	<div <?php $this->dataAttrWrite($args);?> >
	<a href="#" class="text-decoration-none" data-refresh=""><?php iconRefreshWrite();?></a>

	<div class="table-responsive">
	<table class="table table-striped table-hover table-sm">
		<thead>
			<tr>
				<?php
				$headRow=$args["head"];
				if ($headRow==null) $headRow=array_keys($args["rows"][0]);
				 foreach($headRow as $head) { 
						if ($head=="rowClazz") continue;
						if (in_array($head,$hiddenColumns)) continue;
						$rows=$args["rows"];
						$td=count($rows)>0 ? $rows[0][$head] : "";
						$row_clazz=is_numeric(str_replace(",","",$td)) ? "number":"text";
					?>
				<th scope="col" class="col-<?php echo $row_clazz; ?>">
					<?php echo $this->translate($head); ?>
				</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($args["rows"] as $row) { 
					$rowClazz=array_key_exists("rowClazz",$row) ? $row["rowClazz"] : "";
				?>
			<tr>
				<?php foreach($row as $key=>$td) { 
					if ($key=="rowClazz") continue;
					if (in_array($key,$hiddenColumns)) continue;
					$row_clazz=is_numeric(str_replace(",","",$td)) ? "number":"text";
				?>
				<td scope="row" class="col-<?php echo $row_clazz?> <?php echo $rowClazz?>">
					<?php echo $this->translate($td); ?>
					<!-- EDIT -->
					<?php
					 ?>
				</td>
				<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	</div>

	</div>
	<?php 
	} 

	function editFieldRender($key,$value) {
		printf('<input name="edit_%s" type="text" value="%s"/>',$key,$value);

	}
} 
?>