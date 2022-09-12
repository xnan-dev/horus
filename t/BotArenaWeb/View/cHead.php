<?php 
namespace BotArenaWeb\View;
use BotArenaWeb;

class cHeadTitle extends cView {
    function render($args=array("title"=>"the head title")) {
        return $this->param($args,"title");
    }    
}
class cHead extends cView {

    function render($args=array()) { ?>



<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">


    <link1 rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

    <title><?php echo (new cHeadTitle())->render(array("title"=>"Trurl Markets"));?></title>

<!--    <        <meta name="description" content="Source code generated using layoutit.com">
        <meta name="author" content="LayoutIt!"> -->
<!--    <link rel1="stylesheet" href="http://www.atlasestateagents.co.uk/css/tether.min.css"> -->
        
        <link href="css/style.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script language="javascript">            
            <?php include_once("cHeadJs.php"); ?>
        </script>
        <style>
/*            li.nav-item {
                margin-bottom:5pt;
                margin-right:10pt;
            }
*/
            td.col-number, th.col-number {
                text-align:right;            
            }

            .my-right {
                text-align:right !important;
            }
        </style>
     </head>
 <?php } 
}
 ?>