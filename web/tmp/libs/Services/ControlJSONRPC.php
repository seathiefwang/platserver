<?php

//给JSONRPC服务器发送数据
function _send($cont) 
{
    set_time_limit(0);  
    $buff = "";
    $host = "localhost";  
    $port = 5000;  
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); // 创建一个Socket  
    $connection = socket_connect($socket, $host, $port);    //  连接  

    if ($connection)
    {
        socket_write($socket, $cont); // 数据传送 向服务器发送消息  
        $buff = socket_read($socket, 1024, PHP_BINARY_READ);  
        //socket_close($this->socket);  
    }
    return $buff;
} 

//$json=array("id"=>"21459b04","code"=>100, "param"=>1, "data"=>"","response"=>1);
//$json=array("id"=>"1341F401","code"=>0, "param"=>1, "data"=>"1","response"=>0);
//echo _send (json_encode($json));
$json=array("id"=>"1341F401","code"=>102, "param"=>1, "data"=>"1","response"=>0);
echo _send (json_encode($json));

?>
