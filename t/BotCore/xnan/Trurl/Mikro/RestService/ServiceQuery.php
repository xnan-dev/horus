<?php

namespace xnan\Trurl\Mikro\RestService;

class ServiceQuery {
	var $category,$name,$query,$format,$timeoutFn;
	
	function __construct($category,$name,$query,$format,$timeoutFn=null) {
		$this->category=$category;
		$this->name=$name;
		$this->query=$query;
		$this->format=$format;
		$this->timeoutFn=$timeoutFn;
	}

	function category() { return $this->category; }
	function name() { return $this->name; }
	function query() { return $this->query; }
	function format() { return $this->format; }
	function timeoutFn() { return $this->timeoutFn; }
}

?>