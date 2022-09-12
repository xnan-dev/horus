<?php

namespace xnan\HydraTest;


chdir("..");
require("autoloader.php");
require '../vendor/autoload.php';

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Hydra: Shortcuts
use xnan\Trurl\Hydra;
use xnan\Trurl\Hydra\HMaps;
Hydra\Functions::Load;

//Uses: End

function settings() {
	return (new Hydra\HPdoSettings())
		->withHostname("localhost")
		->withDatabase("horus_t")
		->withUser("root")
		->withPassword("root11");
}

function testPdoMap() {

	Hydra\Hydra()->settings(settings());

	$m=new HMaps\HPdoMap(1,"testMap1");
	$m->hydrate();
	print_r($m->values());
	$v1="v1"." ".random_int(1,1000);
	$v2="v2"." ".random_int(1,1000);	
	$m->set("k1",$v1);
	$m->set("k2",$v2);

	$m->dehydrate();
}

function testTypes() {
	$m=new HMaps\HPdoMap(2,"testMap2");
	$m->hydrate();
	$arr=[1,2];
	$m->set("k1",$arr);
	$m->dehydrate();
}
testPdoMap();
//testTypes();

?>