<?php

namespace xnan\MarketBotRunner;

use xnan\Trurl\Horus\PdoSettings;

chdir( __DIR__ );

require("autoloader.php");
require '../vendor/autoload.php';

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Hydra: Shortcuts
use xnan\Trurl\Hydra;
Hydra\Functions::Load;

// Uses: Hydra: Shortcuts
use xnan\Trurl\Hydra\HMatrixes;
use xnan\Trurl\Hydra\HMatrixes\HPdoMatrix;
Hydra\Functions::Load;


/*	
	$r1=(HRefs\HRefs::instance())->createHRef("ref1");
	$v=[1,2,3,4];
	$r1->set($v);	*/  
	
	
/*
	$m=(HMaps\HMaps::instance())->retrieveHMap(1050);
	print time()."<br>";
	$m->hydrateIfReq();
	print time()."</br>";
	print "NAME:".$m->name()."<br>";
	print "<pre>";
	print_r($m);
	print"</pre>";
	exit("FIN");*/



//testCube();

/*
class W {
	var $m1;
	var $m2;
	static $instance=null;

	function __construct() {	
		print "CONSTRUCT!<br>";
		$this->m1=(HMaps::Instance())->createHMap();
		$this->m2=(HMaps::instance())->createHMap();
		

		for ($i=0;$i<50;$i++) {
			$v=$i*$i;
			$this->m1->set($i,$v);			
		}

		for ($i=0;$i<50;$i++) {
			$v=$i*$i;
			$this->m2->set($i,$v);
		}
	}

	static function instance() {
		if (W::$instance==null)  {			
			$mi=(HMaps::instance())->retrieveOrCreateHMap(999);
			if ($mi->hasKey("w")) {
				W::$instance=$mi->get("w");
			} else {
				print "NOKEY<br>";
				W::$instance=new W();
				$mi->set("w",W::$instance);
			}
		}
		return W::$instance;
	}

	function test() {		
		for ($i=0;$i<50;$i++) printf("m1: key:$i val:%s<br>",$i,$this->m1->get($i,$i));
		for ($i=0;$i<50;$i++) printf("m2: key:$i val:%s<br>",$i,$this->m2->get($i,$i));
	}
}

(HMaps::instance())->hydrate();

$w=W::instance();

printf("w size start:%s<br>",strlen(serialize($w)));
$w->test();
printf("w size end:%s<br>",strlen(serialize($w)));
print_r(array_keys((HMaps::instance())->hmaps));
(HMaps::instance())->dehydrate();

printf("w size after:%s<br>",strlen(serialize($w)));

print_r((HMaps::instance()));

exit();
*/

/*
	class T {
		function a() {
			sleep(1);
			print "a\n";
		}
		function b() {
			sleep(2);			
			print "b\n";
		}

		function c() {
			sleep(0);
			print "c\n";
		}
	}

	$performance=new Performance\Performance();
	$tt=new T();
	$t=new Performance\PerformanceWrapper($performance,$tt,["a","b"]);
	$t->a();
	$t->b();
	$t->c();


	$performance->textFormat("text");
	$performance->summaryWrite();*/


function testPack() {
	$spack=b"";
	for ($i=0;$i<10;$i++) $spack.=pack("g",$i+0.5);
	$unpacked=unpack("g",$spack,4*5);
	print_r($unpacked);
}


function testMatrix() {

	$m=(Hydra\hydra())->matrixes()->retrieveOrCreateHMatrix(1,"testMatrix",[7,26]);		

		for ($b=0;$b<26;$b++) {
			for ($a=0;$a<7;$a++) {
				$v=($a+$b)*1.01;
				$m->set([$a,$b],$v);
				printf("set %s,%s:%s<br>",$a,$b,$v );
			}	
		}

	for ($b=0;$b<26;$b++) {
		for ($a=0;$a<7;$a++) {
			printf("value %s,%s: %s<br>",$a,$b,$m->get([$a,$b])); 			
		}
	}


	(Hydra\hydra())->dehydrate();
}

function pdoSettings() {
  return 
    (new PdoSettings\PdoSettings())
    ->withHostname("localhost")
    ->withDatabase("horus_t")
    ->withUser("root")
    ->withPassword("root11");
}



class ValObj1 {
	var $val1=1;
}

function testPdoMatrix() {
	$pdoSettings=pdoSettings();

	$pdo = new \PDO(
		    sprintf('mysql:host=%s;dbname=%s',
		    	$pdoSettings->hostname(),
		    	$pdoSettings->database()),
			    $pdoSettings->user(),
			    $pdoSettings->password());	

	$m=new HPdoMatrix($pdo,"testMatrix",[7,26]);

	$m->hydrate();

		for ($b=0;$b<26;$b++) {
			for ($a=0;$a<7;$a++) {
				$v=($a+$b)*1.01;
				$m->set([$a,$b],$v);
				printf("set %s,%s:%s<br>",$a,$b,$v );
			}	
		}

	$m->dehydrate();

	$m1=new HPdoMatrix($pdo,"testMatrix",[7,26]);
	$m1->hydrate();


	for ($b=0;$b<26;$b++) {
		for ($a=0;$a<7;$a++) {
			printf("value %s,%s: %s<br>",$a,$b,$m1->get([$a,$b])); 			
		}
	}


	$m1->dehydrate();
}


function testHRef() {
	print "*** PRIMERA PARTE: <br>";
	(Hydra\hydra())->hydrate();
	$ref1=(Hydra\hydra())->refs()->retrieveOrCreateHRef(1,"testHref");
	$ref2=(Hydra\hydra())->refs()->retrieveOrCreateHRef(2,"testHref");

	$o1=new ValObj1();
	$o1->val1=10;

	print "<br>** SET 1 <br>";
	$ref1->set($o1);
	print "<br>** SET 2 <br>";
	$ref2->set($o1);
	print "SAMPLE-GET-2: ".$ref2->get()->val1."<br>";

	$ref2->get()->val1=20;

	print "** DEHYDRATE GENERAL: <br>";
	(Hydra\hydra())->dehydrate();

	print "<br>*** SEGUNDA PARTE: <br>";
	print "** RETRIEVE 1<br>";
	$ref1=(Hydra\hydra())->refs()->retrieveOrCreateHRef(1,"testHref");
	print "** RETRIEVE 2<br>";
	$ref2=(Hydra\hydra())->refs()->retrieveOrCreateHRef(2,"testHref");
	print "** GET 1<br>";
	print "value1:".$ref1->get()->val1."<br>";
	print "** GET 2<br>";
	print "value2:".$ref2->get()->val1."<br>";

	print "** DEHYDRATE GENERAL: <br>";
	(Hydra\hydra())->dehydrate();	
}
//testMatrix2();
//testHRef();
testPdoMatrix();

?>