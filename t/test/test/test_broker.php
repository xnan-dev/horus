<?php

function test_broker_login() {
	$ch = curl_init();
	$fp = fopen("http://localhost/test/test_broker_login.php", "r");

	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL,"http://localhost/test/test_broker_login.php");
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

$ret=test_broker_login();
if ($ret) echo "login ok"; else echo "login fail";

?>