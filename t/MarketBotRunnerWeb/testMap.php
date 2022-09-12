<?php

namespace tests;

chdir( __DIR__ );

require("autoloader.php");
require '../vendor/autoload.php';

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Hydra: Shortcuts
use xnan\Trurl\Hydra;
Hydra\Functions::Load;

class ValObj1 {
	var $val1=1;
}

function testHMap() {
	print "*** PRIMERA PARTE:\n";
	Hydra\hydra()->hydrate();
	$map1=Hydra\hydra()->maps()->retrieveOrCreateHMap(1,"testMap1");
	$map2=Hydra\hydra()->maps()->retrieveOrCreateHMap(2,"testMap2");

	for ($i=0;$i<10;$i++) {
		$oi1=new ValObj1();
		$oi1->val1=10*$i;

		$oi2=new ValObj1();
		$oi2->val1=100*$i;
		
		$map1->set("K$i",$oi1);
		$map2->set("K$i",$oi2);
	}

	print "** CONTENIDO SETEADO:\n";
	
	print "<pre>";
	print_r($map1);
	print_r($map2);
	print "</pre>";

	Hydra\hydra()->dehydrate();

	print "** SEGUNDA PARTE:\n";

	Hydra\hydra()->hydrate();

	$map1=Hydra\hydra()->maps()->retrieveOrCreateHMap(1,"testMap1");
	$map2=Hydra\hydra()->maps()->retrieveOrCreateHMap(2,"testMap2");

	$map1->hydrateIfReq();
	$map2->hydrateIfReq();

	print "<pre>";
	print_r($map1);
	print_r($map2);
	print "</pre>";

	Hydra\hydra()->dehydrate();	
}

testHMap();


?>