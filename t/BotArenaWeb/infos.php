<?php

$infos=array();
function addInfo($info) {
	global $infos;
	$infos[]=$info;
}

function infos() {
	global $infos;	
	return $infos==null ? array():$infos;
}

?>