<?php
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;
use xnan\Trurl\Horus\AssetTradeOperation;

AssetTradeOperation\Functions::Load;

class cBotSuggestion extends View\cCardFeatured {
  function renderHeader($args) {
            $suggestion=$args["suggestion"];
            $botArenaId=botArenaId();
            $queueId=$suggestion["queueId"];
            $traderId=$suggestion["traderId"];
            $assetId=$suggestion["assetId"];
            $quantity=$suggestion["quantity"];
            $limitQuote=$suggestion["limitQuote"];
            $currentQuote=$suggestion["currentQuote"];
            $quote=number_format($limitQuote,4);
            $btnClass=$suggestion["tradeOp"]=="Buy" ?  "btn-success" : "btn-danger";
            $opMsg=$suggestion["tradeOp"]=="Buy" ?  "Comprar" : "Vender ";

     (new View\cAcceptCancelButton())->render(array("title"=>"$opMsg <b>$assetId</b>","subtitle"=>"","btn-class"=>$btnClass,"data-cViewToRefresh"=>"BotArenaWeb:PanelBotSuggestions","data-botArenaId"=>$botArenaId,
          "data-viewRefreshQuery"=>$viewReFreshQuery,
        "data-traderId"=>$traderId,"data-queueId"=>$queueId,"acceptAction"=>"tradeOpAccept","cancelAction"=>"tradeOpCancel" ));
    
  }

  function renderTitle($args) { 

  }


  function copyIcon($refNode) {
    $copyData=sprintf('data-copy="%s"  data-bs-toggle="popover" title="Copiado a clipboard" data-bs-content="ya se puede pegar"',$refNode);
    View\iconWrite2("sbeaties-fill",$copyData);
  }

  function renderContent($args) {
      $suggestion=$args["suggestion"];
      $quantity=$suggestion["quantity"];
      $limitQuote=$suggestion["limitQuote"];
      $currentQuote=$suggestion["currentQuote"];

      ?>

      <div class="container">
        <div class="row align-items-start">
          <div class="col text-end text-nowrap"><b><?php echo $this->translate("quantity")?></b></div>
          <div class="col text-end text-nowrap"><span class="quantity"><?php echo $quantity;?></span>&nbsp;<?php $this->copyIcon(".quantity");?></div>    
        </div>
      </div>
      <hr>
      <div class="container">
        <div class="row align-items-start">
          <div class="col text-end text-nowrap"><b><?php echo $this->translate("quote")?></b></div>
          <div class="col text-end text-nowrap"><span class="quote"><?php echo $currentQuote;?></span>&nbsp;<?php $this->copyIcon(".quote");?></div>    
        </div>
      </div>
      <hr>
      <div class="container">
        <div class="row align-items-start">
          <div class="col text-end text-nowrap"><b><?php echo $this->translate("limitQuote")?></b></div>
          <div class="col text-end text-nowrap"><span class="limitQuote"><?php echo $limitQuote;?></span>&nbsp;<?php $this->copyIcon(".limitQuote");?></div>
        </div>
      </div>
      <?php            
  }

  function renderFooter($args) {
    
  }
}

class PanelBotSuggestions  extends  View\cView {
 function render($args=[]) {    

  $suggestionRows = callServiceCsv(botSuggestionsUrl(botArenaId(),traderId(),"text"),true,defaultShortCacheTtl());    
  
  //array_shift($suggestionRows);  

  //print "<pre>";
  //print_r($suggestionRows);
  //print "</pre>";
  $this->renderInfos($suggestionRows);

  $viewReFreshQuery=sprintf("botArenaId=%s&traderId=%s",botArenaId(),traderId());
  ?>
  <div class="container-fluid" data-cView="BotArenaWeb:PanelBotSuggestions" 
  data-viewRefreshQuery="<?php echo $viewReFreshQuery ?>"
   data-cViewRefreshMillis="<?php echo defaultViewRefreshMillis(); ?>">

    <?php 

      if (marketUseHistory()) $this->renderMarketTestControls();    

      $infos=infos();     
      if (count($infos)>0) {
        (new View\cInfo())->render(array("title"=>$infos[0],"div-class"=>"alert-primary")); 
      } 
      
        if (!traderIsSuspended() && !traderAutoApprove() && count($suggestionRows)>0) {
        ?>
        <div class="row justify-content-md-center">

          <ul class="nav nav-pills">
          <?php 

          foreach($suggestionRows as $suggestion) { 
            //if ($suggestion["doable"]=="false") continue;

            $botArenaId=botArenaId();
          ?>
            <?php (new cBotSuggestion())->render(["suggestion"=>$suggestion]); ?>
       <?php } ?>
        </ul>
       </div>

<?php      
    } 

  ?>
  </div> 
<?php 
}

  function renderMarketTestControls() {
    $botArenaId=botArenaId();
    (new View\cTitleBar())->render(["title"=>"Zona de Pruebas","divClass"=>"secondary"]);
    
    $pendingBeats=(marketFinalBeat()-marketBeat());
    $runUrl=sprintf("settingsKey=botWorld.run&settingsValue=true&beats=%s&botArenaId=%s",$pendingBeats,$botArenaId);
    $runUrlParam=sprintf("settingsKey=botWorld.run&settingsValue=true&beats=<BEATS>&botArenaId=%s",$botArenaId);

    ?>
    <div class="container-sm">
      <div class="mb-3">
          <label for="marketBeat" class="form-label">Pulso Actual</label>
          <input type="number" class="form-control form-control-sm" id="marketBeat" placeholder="<?php echo marketBeat();?>" disabled>
          <label for="marketFinalBeat" class="form-label">Pulsos Adicionales</label>
          <input type="number" class="form-control form-control-sm" id="marketFinalBeat" placeholder="<?php echo $pendingBeats ;?>" onChange="return runBeatsChange();">
      </div>
      <div class="mb-3">
        <button type="button" class="btn btn-secondary" data-saveUrl="settingsKey=botArena.reset&settingsValue=true&botArenaId=<?php echo $botArenaId;?>">Resetear</button>
        <button type="button" class="btn btn-primary"
         id="botArenaRun"
         data-saveUrl="<?php echo $runUrl; ?>" 
         data-saveUrlParam="<?php echo $runUrlParam; ?>"
         >Iniciar</button>
      </div>
    </div>
    <?php
  }


  function renderInfos($suggestionRows) {
      if (!marketUseHistory() && marketScheduleTodayIsClosed()) (new View\cInfo())->render(array("title"=>"El mercado <b>hoy</b> está cerrado","divClass"=>"info")); 
      else if (!marketUseHistory() && marketScheduleIsClosed()) (new View\cInfo())->render(array("title"=>"El mercado está cerrado","divClass"=>"info")); 
      else if (!marketUseHistory() && marketPollContentOutdated()) (new View\cInfo())->render(array("title"=>"Las cotizaciones están desactualizadas:".date_format(new \DateTime(),'Y-m-d H:i:s'),"divClass"=>"danger")); 

      if (traderIsSuspended()) (new View\cInfo())->render(array("title"=>"El bot está suspendido","divClass"=>"danger")); 

      if (!marketUseHistory()) {
        if (traderAutoApprove()) {
          (new View\cInfo())->render(array("title"=>"Para este bot las señales se aprueban automáticamente","divClass"=>"warning")); 
        } else if (!traderIsSuspended() && count($suggestionRows)==0) {
          (new View\cInfo())->render(array("title"=>"No hay sugerencias","div-class"=>"alert-primary")); 
        }
      }
  }

}

?>