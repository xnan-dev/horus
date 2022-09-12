<?php
namespace BotArenaWeb\View;

class cView {
    function translate($key) {
        $trans=array(
            "assetId"=>"Activo",
            "traderId"=>"Trader",
            "assetQuantity"=>"Cantidad",
            "limitQuote"=>"Cotización Límite",
            "currentQuote"=>"Cotización Actual",
            "doneQuote"=>"Cotización Operada",
            "assetQuote"=>"Cotización",
            "buyQuote"=>"Cotización",
            "marketBeat"=>"Momento",
            "beat"=>"Momento",
            "portfolioValuation"=>"Valuación de Cartera",
            "queueId"=>"ID Cola",
            "tradeOp"=>"Operación",
            "quantity"=>"Cantidad",
            "quote"=>"Cotización",
            "limitQuote"=>"Cotización Límite",
            "status"=>"Estado",
            "doable"=>"Realizable",
            "statusChangeBeat"=>"P. Actualizado",
            "statusChangeTime"=>"Actualizado",
            "doneTime"=>"Operado",
            "doneBeat"=>"P. Operado",
            "doneBalance"=>"Saldo",
            "settingsKey"=>"Clave",
            "settingsDescription"=>"Descripción",
            "settingsValue"=>"Valor",
            "value"=>"Valor",
            "min"=>"Mínimo",
            "max"=>"Máximo",
            "mean"=>"Media",
            "cicle"=>"Ciclo",
            "valuation"=>"Valuación",
            "maxBuyByStrategy"=>"Máxima compra permitida",
            "Buy"=>"Compra",
            "Sell"=>"Venta",
            "Accepted"=>"Aceptada",
            "Cancelled"=>"Cancelada",
            "Approved"=>"Aprobada",
            "true"=>"Sí",
            "false"=>"No",
            "reportedDate"=>"Fecha Reportada",
            "pollDate"=>"Fecha de Consulta",
            "sellQuote"=>"Cotización Venta",
            "maxBuyQuantityByStrategy"=>"Máxima cantidad permitida",
            "index"=>"#",
            "monthRoi"=>"Retorno Mensual",
            "doneWait"=>"Espera",
            "waitBeats"=>"Espera",
            "roi"=>"Retorno",
            "synchedBeat"=>"P. Sincronizado",
            "linearSlope"=>"Tendencia"
        );

        if (array_key_exists($key,$trans)) return $trans[$key];
        return $key;
    }

    function dataAttrStr($args) {
    	$s="";
    	foreach($args as $key=>$value) {
    		if (str_starts_with($key,"data-")) {
    			$s.=sprintf(" $key=\"$value\"",$key,$value);
    		}
    	}
    	return $s;
    }

    function dataAttrWrite($args) {
    	echo $this->dataAttrStr($args);
    }

    function param($arr,$key,$default="") {
        if (!is_array($arr)) throw new \exception("arr should by an array");
        if (array_key_exists($key,$arr)) return $arr[$key];
        if (array_key_exists($key,$_GET)) return $_GET[$key];
        if (array_key_exists($key,$_POST)) return $_POST[$key];
        return $default;
    }

    function paramWrite($arr,$key,$default="") {
        echo $this->param($arr,$key,$default);
    }

    function renderRet($args=[]) {
        ob_flush();
        ob_clean();
        $this->render($args);      
        $content=ob_get_contents();
        ob_clean();
        return $content;
    }

    function render($args=[]) {

    }
}

?>