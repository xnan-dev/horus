<?php
namespace BotArenaWeb\View;

class Functions { const Load=1; }

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}


function csvToArray($filename='', $delimiter=',')
{
    if(!file_exists($filename)) throw new exception("file missing");
    if (!is_readable($filename)) throw new exception("file not readable");

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE)
    {                       
        while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
        {

            if(!$header) {
                $header = $row;
            } else {
                $data[] = array_combine($header, $row);                                
            }                               
        }
        fclose($handle);
    }                   
    return $data;
}

if (!function_exists('str_getcsv')) {
    function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\") {
        $fiveMBs = 5 * 1024 * 1024;
        $fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
        fputs($fp, $input);
        rewind($fp);

        $data = fgetcsv($fp, 1000, $delimiter, $enclosure); //  $escape only got added in 5.3.0

        fclose($fp);
        return $data;
    }
}


function csvContentToArray($content='', $delimiter=',')
{
    $fiveMBs = 5 * 1024 * 1024;
    $handle = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
    fputs($handle, $content);
    rewind($handle);

    //print "content:$content";

    $header = NULL;
    $data = array();
    if ($handle !== FALSE)
    {                       
        while (($row = fgetcsv($handle, 5000, $delimiter)) !== FALSE)
        {
    
            if(!$header) {
                $header = $row;
                //print "HEADER-ROW:";
                //print_r($row);
                //print "<br>";

            } else {      
               // print "DATA-ROW:";
                //print_r($row);
                //print "<br>";          
                if (count($header)!=count($row)) {                    
                    $data[]=array("csvConvertError"=>"header and row column count missmatch");
                    //print_r($_SERVER);
                    //printf("content1:%s<br>",$content);
                    print "header:<br>";
                    print_r($header);
                    print "row:<br>";
                    print_r($row);                                
                    break;
                } else {
                    //print_r($header);
                    //print_r($row);                                
                    $data[] = array_combine($header, $row);                                
                }
            }                               
        }
        fclose($handle);
    }              
    //if (count($data)==0) $data[]=$header;

    return $data;
}


function iconRefreshWrite() {
?>
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
      <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
      <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
    </svg>
<?php
}

function iconWrite($name) {
    ?>
    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" 
    class="bi bi-<?php echo $name; ?>" viewBox="0 0 20 20">
  <path fill-rule="evenodd" d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6zm5-.793V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"></path>
  <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"></path>
  </svg>
    <?php
  }

  function iconWrite2($name,$extras="") {
    printf('<i class="bi bi-%s" %s></i>',$name,$extras);
  }
    
?>