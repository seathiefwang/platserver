<?php

require_once (__DIR__."/../libs/Action.php");

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache'); 

$string = file_get_contents("php://input"); 
//$array = json_decode($json_string, true); //解析post数据

$uuid = $_GET["u"];//用户email
$mid = $_GET["mid"];//房子主设备ID
$vercode = $_GET["vercode"];//用户验证码



echo Action::run($string, $mid, $uuid);

?>
