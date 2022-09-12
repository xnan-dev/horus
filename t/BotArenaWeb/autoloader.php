<?php
spl_autoload_register(function($class) {
	$file=str_replace('\\','/',$class);
	$file.=".php";
	$ns_file=dirname($file);
	$frags=explode("/",$ns_file);	
	if (count($frags)>0) {
		$ns_file.="/".$frags[count($frags)-1].".php";
	}

	$parent_file="../BotCore/$file";
	$parent_ns_file="../BotCore/$ns_file";
	$parent_direct_file="../$file";
	//print "pidiendo archivo $class=>'$file' , ns_file:'$ns_file'\n";
	if (file_exists($file)) {
		require_once($file);	
	} else if (file_exists($ns_file)) {
		require_once($ns_file);	
	} else if (file_exists($parent_file)) {
		require_once($parent_file);
	} else if (file_exists($parent_ns_file)) {
		require_once($parent_ns_file);
	} else if (file_exists($parent_direct_file)) {
		require_once($parent_direct_file);
	} else {
		$cwd=getcwd();
		print "<b>error:</b> cannot load class $class\n<br>with class file: $file\n<br> nor with ns_file: $ns_file\n<br>nor with: parent_file:$parent_file<br>\nnor with: parent_ns_file:$parent_ns_file<br>\nparent_direct_file:$parent_direct_file<br>\ncwd:$cwd<br>\n";
		throw new \exception("unable to load class");
	}
	
});
?>
