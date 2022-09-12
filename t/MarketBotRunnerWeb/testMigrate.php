<?php

class A {
	var $v1;
	var $version;
	var $v2;

	function __construct() {
		$this->version=1;		
		$v1=4;
		$v2="Valor 2";
		echo "CONSTRUCT!<br>\n";
	}

	function migrateToV1() {
		echo "MIGRATED!\n<br>";
		$this->version=1;
		$this->v1=4;
	}

	function migrateToV2() {
		echo "MIGRATED!\n<br>";
		$this->version=2;
		$this->v2="Valor 2";
	}

	function migrateToV3() {
		echo "MIGRATED!\n<br>";
		$this->version=2;
		unset($this->v11);
	}

	function migrate() {
		if ($this->version()==0) $this->migrateToV1();
		if ($this->version()==1) $this->migrateToV2();
		if ($this->version()==2) $this->migrateToV3();
		echo "migrate<br>\n";
	}

	function __wakeup() {
		$this->migrate();
	}

	function version() {
		return $this->version==null ? 0 : $this->version;
	}
}

//$a=new A();
//file_put_contents("a.serialized",serialize($a));

$a=unserialize(file_get_contents("a.serialized"));

print_r($a);

?>