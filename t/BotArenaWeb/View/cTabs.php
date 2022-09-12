<?php
namespace BotArenaWeb\View;

abstract class cTabs extends cView {
 function render($args=array()) { 
    //https://getbootstrap.com/docs/4.5/components/card/
  ?>

<!--<
nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
    <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">Home</button>
    <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Profile</button>
    <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Contact</button>
  </div>
</nav>
<div class="tab-content" id="nav-tabContent">
  <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">.A..</div>
  <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">.B..</div>
  <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">.C..</div>
</div>
-->


  <nav>
    <div class="nav nav-tabs" id="nav-tab2" role="tablist">
        <?php
         $index=0;
         foreach ($this->tabs() as $key=>$value) { ?>
            <button id="<?php echo $key;?>" 
                 data-toggle="tab"
                 class="nav-link <?php if($index==0) echo " active";?>"
                 data-bs-toggle="tab" 
                 data-bs-target="#c<?php echo $key;?>" 
                 type="button" 
                 role="tab"
                 aria-controls="c<?php echo $key;?>" 
                 ><?php echo $value; ?></button>          
        <?php 
          ++$index;
      } ?>
    </div>
  </nav>

  <div class="tab-content">      
  <?php 
    $index=0;
    foreach($this->tabs() as $key=>$value)  { 
    ?>
      <div class="tab-pane <?php if ($index==0) echo "show active"; ?>" id="c<?php echo $key;?>" role="tabpanel" aria-labelledby="<?php echo $key?>">
        <div class="container-fluid mt-3 mb-3">
          <?php $this->renderTab($index);?>          
        </div>  
      </div>
  <?php 
      ++$index;
  } ?>        
  </div>

<?php 
    } 

    abstract function renderTab($index);

    abstract function tabs();
}
?>