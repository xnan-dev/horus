<?php 
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

class vPlot extends View\cView {
  /* https://www.chartjs.org */

    function render($args=[]) {
    ?>

    <div class="container-fluid">
    <script>
      var marketHistory=null;
    </script>

    <canvas id="myChart" width="400" height="150"></canvas>
    <script>

      var ctx = document.getElementById('myChart');

     marketHistory=<?php echo $this->plotValuesAsJson(botArenaId(),traderId());?>

      var myChart = new Chart(ctx, {
          type: 'line',
          data: marketHistory,
          options: {
              scales: {
                  y: {
                      beginAtZero: true
                  }
              }
          }
      });
  </script>  


</div>
<?php
  }

}

class PlotMarketHistory extends vPlot {  
  function plotValuesAsJson($botArenaId,$traderId) {      
    return  my_file_get_contents(traderHistoryAsJsonUrl($botArenaId,$traderId)); 
  }
}

?>
