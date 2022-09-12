
function viewReplace(data) {
  alert(data);
  var cViewAttr=$(data).attr("data-cView");
  //alert("[data-cView='"+cViewAttr+"']");
  //alert($("[data-cView='"+cViewAttr+"']").length);
  $("[data-cView='"+cViewAttr+"']").replaceWith($(data));
  setupDataAction();
  setupRefreshAction();
  //setupConfirmations();
}

function loadingShow() {
  $("#loading").removeClass("d-none");
}

function loadingHide() {
  $("#loading").removeClass("d-block");
}

function viewRefresh(cView,viewRefreshQuery) {
//  alert(cView+"|"+viewRefreshQuery);
  loadingShow();
  $.ajax({
    url: "index.php?cView=View\\cViewRefresh&cViewToRefresh="+cView+"&"+viewRefreshQuery,
    context: document.body
  }).done(function(data) {    
      viewReplace(data);      
      loadingHide();
      //alert('REEMPLAZADO '+data);
  });
}

function runBeatsChange(e) {
  var value=$("#marketFinalBeat").val();
  var saveUrlParam=$("#botArenaRun").attr("data-saveUrlParam");
  var saveUrlUpdated=saveUrlParam.replace("<BEATS>",value);
  $("#botArenaRun").attr("data-saveUrl",saveUrlUpdated);
}

function checkViewRefresh() {    
  $("[data-cViewRefreshMillis]").each(function() {        
    viewRefresh($(this).attr("data-cView"),$(this).attr("data-viewRefreshQuery"));
  });
}

  function tradeAction(action,e) {
    var traderId=$(e).attr("data-traderId");
    var queueId=$(e).attr("data-queueId");
    var botArenaId=$(e).attr("data-botArenaId");
    var cView="View\\cViewRefresh";
    var cViewToRefresh=$(e).attr("data-cViewToRefresh");
 
    loadingShow();     
    $.ajax({
      url: `index.php?cache=false&cView=${cView}&cViewToRefresh=${cViewToRefresh}&action=BotArenaWeb\\${action}&&botArenaId=${botArenaId}&traderId=${traderId}&queueId=${queueId}`,
      context: document.body
    }).done(function(data) {      
        viewReplace(data);
        loadingHide();
    });
  } 

function refreshAction(e) {
  var view=$(e).parent();
  //alert($(view).length);
  //alert($(view).attr("data-cView"));
  //alert($(view).attr("data-viewRefreshQuery"));
  viewRefresh($(view).attr("data-cView"),$(view).attr("data-viewRefreshQuery"));
} 

function tradeOpCancel(e) {
   //alert('tradeOpCancelJS'); 
   tradeAction('tradeOpCancel',e);
}

function tradeOpAccept(e) {
   //alert('tradeOpCancelJS'); 
   tradeAction('tradeOpAccept',e);
}

function traderSuspend(e) {   
   tradeAction('traderSuspend',e);
}

function traderResume(e) {
   //alert('tradeOpCancelJS'); 
   tradeAction('traderResume',e);
}

function actionClick(e) {   
  //$(e).confirmation('show');
    if (confirm("confirma la operación?")) {
      var actionFn=$(e).attr("data-action");
      //alert("actionFn:"+actionFn);
      window[actionFn]([e]);      
    }
}

function setupDataAction() {
  $("[data-action]").each(function () {
    if ($(this).attr("data-action-bound")!="true") {
      $(this).attr("data-action-bound","true");
      $(this).click(function() {
        actionClick(this);
      });      
    }
  });

}

function setupDataCopy() {
  $("[data-copy]").each(function () {
    if ($(this).attr("data-copy-bound")!="true") {
      $(this).attr("data-copy-bound","true");
      $(this).click(function() {
        copyClick(this);
      });      
    }
  });

}


function setupRefreshAction() {
  $("[data-refresh]").each(function () {
    if ($(this).attr("data-refresh-bound")!="true") {
      $(this).attr("data-refresh-bound","true");
      $(this).click(function() {
        refreshAction(this);
      });      
    }
  });
}

function setupConfirmations() {
  $('[data-toggle=confirmation]').confirmation({
    rootSelector: '[data-toggle=confirmation]',
    // other options
  });  
}

  function chartUpdateData(newLabels,newDatasets) {
    myChart.data.labels = newLabels;
    myChart.data.datasets = newDataset;
    myChart.update();
  }

function chartUpdate() {
  $.ajax({
    url: traderHistoryAsJsonUrl(),
    dataType: "script",
    context: document.body    
  }).done(function(data) {    
    console.log('lamado2');
    console.log(data.labels,data.datasets);
  });
}

var GetChartData = function () {
    $.ajax({
        url: traderHistoryAsJsonUrl(),
        method: 'GET',
        dataType: 'text',
        context: document.body,
        success: function (d) {
          eval('data = '+d+';');        
          //console.log(data.labels);
          /*myChart.data.labels.pop();
          myChart.data.datasets.forEach((dataset) => {
              dataset.data.pop();
          });*/
          //myChart.data.labels = data.labels;
          //myChart.data.datasets = data.DataSets;           
          myChart.data = data;
          myChart.options.animation = false;
          myChart.update();          
        }
    });
};

function findParentView(node) {
  if ($(node).attr("data-cView")) return $(node).attr("data-cView");
  return findParentView($(node).parent());
}

function findNodeToRoot(node,selector) {
  var selected=$(node).find(selector);
  if (selected.length>0) return selected;
  return findNodeToRoot($(node).parent(),selector);
}

function copyClick(node) {
  var copySelect=$(node).attr("data-copy");
  var nodeToCopy=findNodeToRoot(node,copySelect);
  copyToClipboard($(nodeToCopy).text());    
  var popover = new bootstrap.Popover(document.querySelector(copySelect), {
    container: 'body'
  })
  popover.show();
  //var tooltip = $(nodeToCopy).tooltip({ placement : "bottom", title : 'ok'});
  //tooltip.show();
}

function cellSave(input) {  
  value=$(input).val();
  saveUrl=$(input).attr("data-saveUrl")+`&settingsValue=${value}`;
  var action="cellSave";
  var cView="View\\cViewRefresh";
  var cViewToRefresh=findParentView(input); //$(e).attr("data-cViewToRefresh");
  var saveUrlEnc=saveUrl.replaceAll(" ","%20");
  saveUrlEnc=encodeURIComponent(saveUrlEnc);
  //alert(value);
  console.log(saveUrl);
  //alert(cViewToRefresh);

  loadingShow();
  $.ajax({
    url: `index.php?cache=false&cView=${cView}&cViewToRefresh=${cViewToRefresh}&action=BotArenaWeb\\${action}&saveUrl=${saveUrlEnc}`,
    context: document.body
  }).done(function(data) {      
      viewReplace(data);
      loadingHide();
  });
}

function buttonSave(input) {  
  saveUrl=$(input).attr("data-saveUrl");
  var action="cellSave";
  var cView="View\\cViewRefresh";
  var cViewToRefresh=findParentView(input);
  var saveUrlEnc=saveUrl.replaceAll(" ","%20");
  saveUrlEnc=encodeURIComponent(saveUrlEnc);

  loadingShow();
  $.ajax({
    url: `index.php?cache=false&cView=${cView}&cViewToRefresh=${cViewToRefresh}&action=BotArenaWeb\\${action}&saveUrl=${saveUrlEnc}`,
    context: document.body
  }).done(function(data) {      
      viewReplace(data);
      loadingHide();
  });
}

function setupEditText()  {
  $("[data-editText]").each(function() {
    saveUrl=$(this).attr("data-saveUrl");
    if (saveUrl.length>0) {
      editText=$(this).attr("data-editText");
      $(this).after(`
        <span class="editPanel">
          <input class="d-none cellEditInput" name="inputText" type="text" data-saveUrl="${saveUrl}" value="${editText}"/>
          &nbsp;<i class="cellEdit bi bi-pencil-square"></i>
          &nbsp;<i class="d-none cellEditSave bi bi-clipboard-check"></i>
          &nbsp;<i class="d-none cellEditCancel bi bi-clipboard-x"></i>
        </span>
        `);

    }
  });
  
  $("button[data-saveUrl]").click(function(e) {
    buttonSave(this);
  });

  $(".cellEdit").click(function (e) {
    $(this).addClass("d-none");
    $(this).parent().prev().addClass("d-none");
    $(this).parent().find(".cellEditInput").removeClass("d-none");
    $(this).parent().find(".cellEditSave").removeClass("d-none");
    $(this).parent().find(".cellEditCancel").removeClass("d-none");
  } );

  $(".cellEditSave").click(function (e) {
    $(this).parent().prev().removeClass("d-none");
    $(this).parent().find(".cellEdit").removeClass("d-none");
    $(this).parent().find(".cellEditInput").addClass("d-none");
    $(this).parent().find(".cellEditSave").addClass("d-none");
    $(this).parent().find(".cellEditCancel").addClass("d-none");
    cellSave($(this).parent().find(".cellEditInput"));
  });

  $(".cellEditCancel").click(function (e) {
    $(this).parent().prev().removeClass("d-none");
    $(this).parent().find(".cellEdit").removeClass("d-none");
    $(this).parent().find(".cellEditInput").addClass("d-none");
    $(this).parent().find(".cellEditSave").addClass("d-none");
    $(this).parent().find(".cellEditCancel").addClass("d-none");      
  });

}

function copyToClipboard(text) {
    var sampleTextarea = document.createElement("textarea");
    document.body.appendChild(sampleTextarea);
    sampleTextarea.value = text; //save main text in it
    sampleTextarea.select(); //select textarea contenrs
    document.execCommand("copy");
    document.body.removeChild(sampleTextarea);
}


function setupPopOvers() {
  var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
  });
}

function chartsUpdate() {
  GetChartData();
  //chartUpdate();
}

$(document.body).ready(function() {
  setupDataCopy();
  setupDataAction();
//  setupRefreshAction();
  setupEditText();
//  setInterval(chartsUpdate,viewRefreshMillis());  
//  setInterval(checkViewRefresh,viewRefreshMillis());


setupPopOvers();

});

//setupConfirmations();

/*
$('.nav-tabs a').click(function (e) {
  e.preventDefault();
  $(this).tab('show');
});

$('.nav-tabs a:first').tab('show');
*/

