<?php
namespace BotArenaWeb\View;

abstract class cAccordion extends cView {
 function render($args=array()) { 
   //https://getbootstrap.com/docs/5.0/components/accordion/
    $id=$this->param($args,"","noname".random_int(1,1000*1000));
  ?>


<div class="accordion" id="<?php echo $id; ?>">  
  <?php 
    $index=0;
    foreach($this->tabs() as $key=>$value)  
    {     
      $rootedKey=$id."_".$key;
    ?>
  

  <div class="accordion-item">
    <h2 class="accordion-header" id="heading<?php echo $rootedKey;?>">
      <button class="accordion-button <?php if($index!=0) echo "collapsed";?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $rootedKey;?>" aria-expanded="<?php echo ($index==0 ? "true":"false") ;?>" aria-controls="<?php echo $rootedKey;?>">
        <?php echo $value; ?>
      </button>
    </h2>
    <div id="<?php echo $rootedKey;?>" class="accordion-collapse collapse <?php if($index==0) echo "show";?>" aria-labelledby="heading<?php echo $rootedKey;?>" data-bs-parent="#<?php echo $id; ?>">
      <div class="accordion-body">
        <?php $this->renderTab($index);?>
      </div>
    </div>
  </div>

    
    <?php 
      ++$index;
    } ?>        



</div>
<?php 
    } 

    function renderTab($index) { return "content #$index"; }

    function tabs() { return []; }
}
?>