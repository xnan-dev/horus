<?php 
namespace BotArenaWeb;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

class PanelBotControls extends View\cView {
  function render($args=[]) {  
    $viewReFreshQuery=sprintf("botArenaId=%s&traderId=%s",botArenaId(),traderId());
  
  ?>
    <span
    data-cView="BotArenaWeb:PanelBotControls"
    data-viewRefreshQuery="<?php echo $viewReFreshQuery ?>"
    data-cViewRefreshMillis="<?php echo defaultViewRefreshMillis(); ?>">      
         <?php // if (!marketUseHistory()) (new View\cTag())->render(["title"=>"En Vivo","badgeType"=>"danger","headingLevel"=>7]);?>
         &nbsp;
         <?php (new View\cTag())->render(["title"=>traderTitle(),"badgeType"=>"primary","headingLevel"=>4]);?>
         &nbsp;     
<?php
    if (traderIsSuspended()) {
  ?>    
    <button type="button" class="btn btn-danger btn-sm"
     data-cViewToRefresh="BotArenaWeb:PanelBotControls" 
     data-botArenaId="<?php echo(botArenaId());?>"       
     data-traderId="<?php echo(traderId());?>"             
     data-action="traderResume" >Reanudar</button>
<?php } else { ?>
    <button type="button" class="btn btn-success btn-sm"
     data-cViewToRefresh="BotArenaWeb:PanelBotControls" 
     data-botArenaId="<?php echo(botArenaId());?>"             
     data-traderId="<?php echo(traderId());?>"             
     data-action="traderSuspend">Suspender</button>    
<?php
     }
     ?> 
   </span> 
    <?php
  }
}
?>
