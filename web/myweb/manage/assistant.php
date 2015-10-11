<?php
require_once (__DIR__."/../base/_Timing.php");



file_put_contents("./test.txt", "run\n", FILE_APPEND);
if (isset($_SERVER['argv'][1])) {   
	$time = $_SERVER['argv'][1];   
} else {   
	$time = 'null';   
}   
  
if (isset($_SERVER['argv'][2])) {   
	$email = $_SERVER['argv'][2];   
} else {   
	$email = 'Zhangsan@test.com';   
}   
  
if (isset($_SERVER['argv'][3])) {   
	$param = $_SERVER['argv'][3];   
} else {   
	$param = 'null';   
}   
  
_Timing::_Enable(array("email"=>$email,"time"=>$time,"param"=>$param));
?>