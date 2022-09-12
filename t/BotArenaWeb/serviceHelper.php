<?php
namespace BotArenaWeb;
use xnan\Trurl;
use xnan\Trurl\Horus;
use BotArenaWeb\View;

function debugWrite($s) {
	if ((new View\cView())->param(array(),"debug","")=="true") print "<b>debug:</b> $s\n<br>";
}


function my_file_get_contents($url) {
	 $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
	  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 5*60);

    $data = curl_exec($ch);
    //print "URL:$url ";
    if ($data===false) exit("my_file_get_contents: url:$url msg:failed\n");
    curl_close($ch);

    return $data;
}


function serviceCacheStore($url,&$array) {
  global $performance;
  $hash=md5($url);  
  checkDiskAvailable();
  $content=serialize($array);  
  file_put_contents("content/cache/service.$hash.serialized",$content);
}

function callServiceCsvCache($url,$cacheable,$ttlSeconds) {
  global $performance;

  if (!$cacheable) return null; 
  if ($ttlSeconds==0) return null;
  
  $hash=md5($url);
  $array=null;
  $file="content/cache/service.$hash.serialized";
  if (file_exists($file)) {
    if (filemtime($file)+$ttlSeconds<time()) {
      unlink($file);
      return null;
      //print "cache deleted by age\n";
    } else {
      if (isDebug()) printf("callServiceCsvCache: cacheHit: withHash:$hash fileTime:%s time:%s<br>",filemtime($file),time());
      $content=file_get_contents($file);
      $array=unserialize($content);

    }
  }
  return $array;
}

function callServiceCsv($url,$cacheable=false,$ttlSeconds=0) {
    global $performance;
    $cacheable=false;
    
    if ((new View\cView())->param(array(),"cache","true")=="false") $cacheable=false;
    //$performance->track("callServiceCsv-$url");
    //$performance->track("callServiceCsv");

    $arrayCache=callServiceCsvCache($url,$cacheable,$ttlSeconds);

    if ($arrayCache!=null) {
        if (isDebug())  {
          print "url:$url cache_hit<br>";
          print "<pre>";
          print_r($arrayCache);
          print "</pre>";          
        }      
        return $arrayCache;
    }

    //$performance->track("callServiceCsv-getcontents");
    //$performance->track("callServiceCsv-getcontents-$url");
    $content=my_file_get_contents($url);
    //$performance->track("callServiceCsv-getcontents");
    //$performance->track("callServiceCsv-getcontents-$url");

    $fatalErrorFound=!(stripos($content,"fatal error")===FALSE);

    if($fatalErrorFound) {
      printf("callServiceCsv: url:%s fail: msg: fatal error found in service response\n",$url);
      exit();
    }

    if(strlen($url)==0) {
      printf("callServiceCsv: url:%s fail: msg: url cannot be empty\n",$url);
      exit();
    }

    $array=View\csvContentToArray($content,';');
    if (!is_array($array)) {
      printf("callServiceCsv: url:%s fail: msg: data should convert to an array\n",$url);
      exit();
    }  
    if ($array===null) {
      printf("callServiceCsv: url:%s msg: array should not be null\n",$url);
      exit();
    }

    if (array_key_exists("csvConvertError",$array)) {
      printf("callServiceCsv: url:%s msg: %s",$url,$array["csvConvertError"]);
      exit();
    }

    if (count($array)>=2 && count($array[0])!=count($array[1])) {
      printf("callServiceCsv: url:%s fail: msg: header and rows should have equal count\n",$url);
      print "<pre>";
      print_r($array);
      print "</pre>";
    }    
    
    if (isDebug())  {
      print "url:$url<br>";
      print "<pre>";
      print_r($array);
      print "</pre>";          
    }

    if ($cacheable) serviceCacheStore($url,$array);
    $hit=$arrayCache !=null;
    //echo "callServiceCsv-$url ### hit:$hit<br>";
    //$performance->track("callServiceCsv-$url");
    //$performance->track("callServiceCsv");

    return $array;
}

?>