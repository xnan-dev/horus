<?php
namespace BotArenaWeb\View;

class cTableMobile extends cAccordion {
  var $args;
  function render($args=array()) { 
    $this->args=$args;
    parent::render($args);
  } 

  function renderTab($index) { 
    $row=$this->args["rows"][$index];
    $hiddenColumns=$this->args["hiddenColumns"];
    $titleColumn=$this->args["titleColumn"];
    foreach($row as $key=>$value) {
      if (in_array($key,$hiddenColumns) || $key=="rowClazz" || $key==$titleColumn) continue;
      ?>
        <div class="container-fluid">
          <div class="row">
            <div class="col text-center text-nowrap"><b><?php echo $this->translate($key);?></b></div>          
          </div>
          <div class="row">
            <div class="col text-center text-nowrap"><span class="value"><?php echo $value; ?></span></div>
          </div>
        </div>
        <hr>

      <?php
    }    
  }

  function tabs() {
    $tabs=[]; 
    foreach($this->args["rows"] as $row) {
      $keys=array_keys($row);
      $titleKey=$this->args["titleColumn"];
      $tabs[]=$row["title"] ? $row["title"] : $row[$titleKey];
    }
    return $tabs;
  }
}
?>