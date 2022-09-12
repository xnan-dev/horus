<?php
namespace BotArenaWeb\View;

class cTableResponsive extends cView {
  function render($args=[])  {
    
    echo '<div class="d-block d-lg-none d-xl-none">';
    (new cTableMobile())->render($args);
    echo '</div>';
    echo '<div class="d-none d-lg-block d-xl-block">';
    (new cTable())->render($args);
    echo '</div>';
  }
  
  /*function tabTitle($row,$key) {
    return $row[$key];
  }*/
}

?>