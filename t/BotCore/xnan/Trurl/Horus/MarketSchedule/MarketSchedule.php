<?php
namespace xnan\Trurl\Horus\MarketSchedule;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Horus\AssetTradeStatus;
use xnan\Trurl\Horus\AssetTradeOrder;
use xnan\Trurl\Horus\CryptoCurrency;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Nano\DataSet;


class MarketSchedule {
	var $marketIsAlwaysOpen=true;
	var $marketOpenHour="00:00";
	var $marketCloseHour="00:00";
	var $marketWeekDaysOpen="0,1,2,3,4,5,6";
	var $marketHolidays="";
	var $marketTimeZone="America/Argentina/Buenos_Aires";
	var $marketBeatSeconds=5;

	function __construct() {
	}

	function scheduleSettingsAsCsv() {
		$ds=new DataSet\DataSet(["settingsKey","settingsDescription","settingsValue"]);
		
		$ds->addRow(["marketIsAlwaysOpen","Si el mercado está siempre activo",$this->marketIsAlwaysOpen]);
		$ds->addRow(["marketOpenHour","Horario de apertura",$this->marketOpenHour]);
		$ds->addRow(["marketCloseHour","Horario de cierre",$this->marketCloseHour]);
		$ds->addRow(["marketWeekDaysOpen","Días de la semana que abre (0:domingo)",$this->marketWeekDaysOpen]);
		$ds->addRow(["marketHolidays","Días feriados (fechas separadas por coma)",$this->marketHolidays]);
		$ds->addRow(["marketTimeZone","Zona horaria del mercado",$this->marketTimeZone]);
		$ds->addRow(["marketBeatSeconds","Duración de un pulso en segundos",$this->marketBeatSeconds]);
		$ds->addRow(["marketIsOpen","Si está abierto el mercado",$this->marketIsOpen()]);
		$ds->addRow(["marketIsTodayOpen","Si está abierto el mercado hoy",$this->marketIsTodayOpen()]);

		return $ds->toCsvRet();		
	}

	function marketOpenHour($marketOpenHour=null) {
		if ($marketOpenHour!=null) $this->marketOpenHour=$marketOpenHour;
		return $this->marketOpenHour;
	}

	function marketCloseHour($marketCloseHour=null) {
		if ($marketCloseHour!=null) $this->marketCloseHour=$marketCloseHour;
		return $this->marketCloseHour;
	}

	function marketOpenHoursCount() {
		$o=$this->marketOpenHour();
		$c=$this->marketCloseHour();
		
		if ($o=="00:00" && $c=="00:00") return 24;

		$of=explode(":",$o);
		$cf=explode(":",$c);
		$ov=0+60*($of[0])+($of[1]);
		$cv=0+60*($cf[0])+($cf[1]);
		$ret=($cv-$ov)/60;
		return $ret;
	}

	function marketTodayOpenTime() {
		if (!$this->marketIsTodayOpen()) return -1;
		$frags=explode(":",$this->marketOpenHour());
		return strtotime(sprintf("today, +%s hour +%s minute",$frags[0],$frags[1]));
	}

	function marketTodayCloseTime() {
		if (!$this->marketIsTodayOpen()) return -1;
		$frags=explode(":",$this->marketCloseHour());
		return strtotime(sprintf("today, +%s hour +%s minute",$frags[0],$frags[1]));
	}

	function marketWeekDaysOpen($marketWeekDaysOpen=null) {
		if ($marketWeekDaysOpen!=null) $this->marketWeekDaysOpen=$marketWeekDaysOpen;
		return $this->marketWeekDaysOpen;
	}

	function marketIsTodayOpen() {
		$daysOpen=explode(",",$this->marketWeekDaysOpen());
		$dayOfWeek=date("N", time());
		$isInDaysOpen=in_array($dayOfWeek,$daysOpen);

		return $this->marketIsAlwaysOpen() || 
			($isInDaysOpen && !$this->marketIsHoliday());
	}

	function marketHolidays($marketHolidays=null) {
		if ($marketHolidays!=null) $this->marketHolidays=$marketHolidays;
		return $this->marketHolidays;
	}

	function marketTimeZone($marketTimeZone=null) {
		if ($marketTimeZone!=null) $this->marketTimeZone=$marketTimeZone;
		return $this->marketTimeZone;
	}


	function marketIsAlwaysOpen($marketIsAlwaysOpen=null) {
		if (!($marketIsAlwaysOpen===null)) $this->marketIsAlwaysOpen=$marketIsAlwaysOpen;		

		return $this->marketIsAlwaysOpen;
	}

	function marketIsHoliday() {
		$date=date("Y-m-d");
		$holidays=explode(",",$this->marketHolidays());
		return in_array($date,$holidays);
	}

	function marketIsOpen() {
		if ($this->marketIsAlwaysOpen()) return true;
		if (!$this->marketIsTodayOpen()) return false;

		$openTime=$this->marketTodayOpenTime();
		$closeTime=$this->marketTodayCloseTime();
		$time=time();
		//print sprintf("time:$time a:%s ($time>$openTime) b:%s ()\n",$time>$openTime,$time<$closeTime);
		return $time>$openTime && $time<$closeTime;
	}


	function marketBeatSeconds($marketBeatSeconds=null) {
		if ($marketBeatSeconds!=null) $this->marketBeatSeconds=$marketBeatSeconds;
		return $this->marketBeatSeconds;
	}

	function marketBeatMinutes() {
		return $this->marketBeatSeconds/60;
	}

	function marketBeatHours() {
		return $this->marketBeatSeconds/60/60;
	}

	function title($title=null) {
		if ($title!=null) $this->title=$title;
		return $this->title;
	}

}


function scheduleWrite($m) {
	print "<pre>";
	printf("********* title:%s\n",$m->title());
	printf("open hour:%s\n",$m->marketOpenHour());
	printf("close hour:%s\n",$m->marketCloseHour());
	printf("today open time:%s\n",$m->marketTodayOpenTime());
	printf("today close time:%s\n",$m->marketTodayCloseTime());
	printf("is open today:%s\n",$m->marketIsTodayOpen());
	printf("week days open:%s\n",$m->marketWeekDaysOpen());
	printf("beat seconds:%s\n",$m->marketBeatSeconds());
	printf("is always open:%s\n",$m->marketIsAlwaysOpen());
	printf("holidays:%s\n",$m->marketHolidays());
	printf("is holiday:%s\n",$m->marketIsHoliday());
	printf("is open:%s\n",$m->marketIsOpen() ? "si":"no");	
	print "</pre>";
}

/*
scheduleWrite(scheduleCryptosBuild());
scheduleWrite(scheduleMervalBuild());
scheduleWrite(scheduleNasdaqBuild());
scheduleWrite(ScheduleNyseBuild());
*/

?>