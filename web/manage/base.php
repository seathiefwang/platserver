<?php

require_once (__DIR__."/../libs/iBeeAir2/core/Event.php");
require_once (__DIR__."/../libs/iBeeAir2/core/Error.php");
require_once (__DIR__."/../libs/Db.php");


header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache');  

//var_dump($_GET);
if (!isset($_GET["u"]))
{
    exit(Error::getRetString(1));  
}
$json_string = file_get_contents("php://input"); 
$array = json_decode($json_string, true); //解析post数据

//执行系统事件
echo Event::onMessage($array, __DIR__."/../libs/iBeeAir2/base/");

file_put_contents("./test.txt", $_GET["u"]."\n".$json_string, FILE_APPEND);

?>