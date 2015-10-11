<?php

/**
 * @author lvxin
 * @version 1.0
 * @created 06-二月-2015 14:30:05
 */
class ControlJSONRPC
{

    private $socket;
    function __construct()
    {
    }

    function __destruct()
    {
    }

    //给JSONRPC服务器发送数据
    private function _send($cont) 
    {
        set_time_limit(0);  
        $buff = "";
        $host = "localhost";  
        $port = 2014;  
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); // 创建一个Socket  
        $connection = socket_connect($this->socket, $host, $port);    //  连接  

        if ($connection)
        {
            socket_write($this->socket, $cont); // 数据传送 向服务器发送消息  
            $buff = socket_read($this->socket, 1024, PHP_BINARY_READ);  
            socket_close($this->socket);  
        }
        return $buff;
    } 

    //使连接号为fid的客户端断开连接
    function DisconnectClient($fid) 
    {
        $method = "closefd";
        $params = array("fid"=>$fid);
        $json = array("method"=>$method, "params"=>$params);

        $_ret = $this->_send(json_encode($json));

        $json = json_decode ($_ret, true);
        if (is_array($json) && array_key_exists("result", $json))
        {
            if ($json["result"] == "OK")
                return true;
        }
        return false;
    }

    function SendTextToFid($from, $to, $fid, $cont){
        $array = array(
            "method"=>"sendto", 
            "params"=>array(
                "from"=>$from,
                "to"=>$to,
                "fid"=>$fid,
                "message"=>array(
                    "type"=>"text",
                    "content"=>urlencode($cont)
                )
            )
        );
        $json = json_encode($array);
        $_ret = $this->_send(urldecode($json));

        //echo $_ret;
        return $_ret;
    }
}
?>
