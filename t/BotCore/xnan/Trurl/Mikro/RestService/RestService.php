<?php

namespace xnan\Trurl\Mikro\RestService;
use xnan\Trurl\Nano;
Nano\Functions::Load;

class RestService {
	var $restServiceId;
	var $timeCreated;
	var $serviceQueries=[];
	var $serviceGroups=[];

	function __construct() {
		$this->timeCreated=time();
		$this->restServiceId=random_int(1,1000000);
		$this->setupGlobals();
		$this->setupServiceQueries();		
	}

	function setupGlobals() {
		set_time_limit(20*60);
		error_reporting(E_ALL ^ E_NOTICE);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		date_default_timezone_set($this->timeZone());
	}

	function restServiceId() {
		return $this->restServiceId;
	}

	function timeZone() {
		return 'America/Argentina/Buenos_Aires';
	}

	function kill() {
	}

	function serviceProcess() {				
		$this->setupGlobals();

		$method="srv".ucfirst($this->prmQ());
		$srvExists=method_exists($this,$method);		
		$srvCsv=Nano\nanoString()->strEndsWith($method,"AsCsv");
		$srvJson=Nano\nanoString()->strEndsWith($method,"AsJson");
		$srvLog=Nano\nanoString()->strEndsWith($method,"AsLog");

		if ($srvExists) {
			if ($srvCsv) {				
				header("Content-Type: text/plain");
				echo $this->$method();
			} else if ($srvLog) {				
				header("Content-Type: text/plain");
				echo $this->$method();
			} else if ($srvJson) {
				header("Content-Type: application/json");
				echo $this->$method();
			} else {
				echo $this->$method();
			}			
		} else if (strlen($this->prmQ())>0) {
			throw new \Exception("unknownQuery");
		} else {
			$this->help();
		}

	}

	function param($key,$default="") {
		return param($key,$default);
	}

	function htmlLink($title=null,$url) {
		if ($title==null) $title=$url;
		return sprintf('<a href="%s">%s</a>',$url,$title);
	}

	function htmlTitle($title) {	
		Nano\msg("");
		Nano\msg("<b>$title</b>");
	}

	function registerServiceQuery($category,$name,$query,$format,$timeoutFn=null) {
		$this->serviceQueries[$name]=new ServiceQuery($category,$name,$query,$format,$timeoutFn);
	}

	function registerServiceGroup($groupName,$groupTitle) {
		$this->serviceGroups[]=["groupName"=>$groupName,"groupTitle"=>$groupTitle];
	}

	function serviceQueries() {
		return $this->serviceQueries;
	}

	function serviceQueriesByGroup($groupName) {
		$ret=[];
		$serviceQueries=$this->serviceQueries();
		foreach($serviceQueries as $query) {
			if ($query->category()==$groupName) {
				$ret[]=$query;
			}
		}
		return $ret;
	}

	function serviceGroups() {
		return $this->serviceGroups;
	}
	
	function setupServiceQueries() {

	}

	function params() {
		$ret=[];
		$methods=get_class_methods($this);
		foreach($methods as $method) {
			if (Nano\nanoString()->strStartsWith($method,"prm")) {
				$prm=lcfirst(str_ireplace("prm","",$method));
				$ret[]=$prm;
			}
		}		
		return $ret;
	}

	function serviceQuerySample($query) {
		$params=$this->params();
		$ret=$query->query();
		foreach($params as $param) {
			$prmMethod="prm".ucfirst($param);			
			$value=$this->$prmMethod();
			$ret=str_replace("{".$param."}",$value,$ret);
		}
		return $ret;
	}

	function help() {		
		foreach($this->serviceGroups() as $group) {
			$this->htmlTitle($group["groupTitle"]);	
			$queries=$this->serviceQueriesByGroup($group["groupName"]);
			foreach($queries as $query) {
				$querySample=$this->serviceQuerySample($query);
				Nano\msg($this->htmlLink($query->name(),$this->serviceQueryToUrl($querySample)));
			}
		}
	}


	function serviceQueryToUrl($query) {
		return sprintf("%s?%s","http://local.sample.com",$query);
	}

}


function param($key,$default="") {
	if (array_key_exists($key,$_GET)) return $_GET[$key];
	return $default;
}


?>
