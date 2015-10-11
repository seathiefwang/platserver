<?php

require_once (__DIR__."/../libs/Event.php");
require_once (__DIR__."/../db/Db.php");


header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache'); 

//var_dump($_GET);
if (!isset($_GET["uuid"])); 
$json_string = file_get_contents("php://input"); 
//$json_string = $GLOBALS['HTTP_RAW_POST_DATA'];
$array = json_decode($json_string, true); //解析post数据

//执行系统事件
echo Event::onMessage($array, __DIR__."/../user/");

?>