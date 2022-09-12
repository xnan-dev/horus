<?php

class TestMarketBroker extends MarketBroker {

	function runLogin() {
		$ch = curl_init();


		$fp = fopen(sprintf("http://%s/test/test_broker_login.php",Trurl\domain()), "r");

		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL,sprintf("http://%s/test/test_broker_login.php",Trurl\domain()) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,"user=miusuario&password=mipassword&submit=enviar");
		/*curl_setopt($ch, CURLOPT_POSTFIELDS, 
	         http_build_query(array('user' => 'miusuario')));
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
	         http_build_query(array('password' => 'mipassword')));*/
		$out=curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		print "out: $out";	
		$ret=$out=="USUARIO LOGUEADO";
		return $ret;
	}
}


/*
date_default_timezone_set("America/Argentina/Buenos_Aires");

function testMarketBrokerBuild() {
	$b=new TestMarketBroker();
	return $b;
}


$b=testMarketBrokerBuild();

$ret=$b->runLogin();
if ($ret) {
 	echo "login ok"; 
} else{
	 echo "login fail";	
}
*/
?>