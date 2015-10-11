<?php
require_once (__DIR__."/libs/Event.php");
require_once (__DIR__."/libs/functions.php");

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache'); 


$json_string = file_get_contents("php://input"); 
Event::onMessage(json_decode ($json_string, true), __DIR__."/libs/Services/extend/");

//if (isset($_GET["u"]))
//{
    //if(verify_user($_GET["u"]) == true)//tdid合法
    //{
        //$json_string = file_get_contents("php://input"); 
    //}
    //else
    //{
        //echo '{"code":33406, "msg":"invalid tdid or u"}';
    //}
//}
//else
//{
        //echo '{"code":33407, "msg":"miss param tdid or u"}';
//}
?>
