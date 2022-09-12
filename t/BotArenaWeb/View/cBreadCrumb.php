<?php
namespace BotArenaWeb\View;

class cBreadCrumb extends cView {

	function render($args=array("title"=>"","links"=>array("title"=>"home","url"=>"index.php","isActive"=>true))) { ?>

<!--https://getbootstrap.com/docs/5.0/components/navbar/ -->
    <span class="h4">
    
    <a class="text-decoration-none"  href="index.php"><?php iconWrite2("house-fill");?></a>
    &nbsp;
    <a class="text-decoration-none"  action="action" onclick="window.history.go(-1); return false;" href="#"><?php iconWrite2("arrow-left-square-fill");?></a>
    &nbsp;&nbsp;&nbsp;
    
		<?php foreach($args["links"] as $link) { ?>
      <a href="<?php $this->paramWrite($link,"url")?>">            
        <?php $this->paramWrite($link,"title")?>
      </a>
		<?php } ?>
    </span>

<?php }
}

?>