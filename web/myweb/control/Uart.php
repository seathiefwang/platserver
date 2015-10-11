<?php

/**
 * 
 * 主逻辑
 * 主要是与uart服务进行通信
 * @author lvxin <lvxinxp@126.com>
 * 
 */

class Uart
{
    /**
     * 给串口服务发送数据
     * @cont string
     */
    public static function send($cont)
    {
        set_time_limit(0);  
        $buff = "";
        $host = "localhost";  
        $port = 5000;

        for ($i=0; $i<3; $i++)
        {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); // 创建一个Socket  
            $connection = socket_connect($socket, $host, $port);    //  连接  

            if ($connection)
            {
                socket_write($socket, $cont); // 数据传送 向服务器发送消息  
                $buff = socket_read($socket, 1024, PHP_BINARY_READ);  
                socket_close($socket);  
            }

            $res = json_decode($buff, true);
            //echo $buff."\n";
            if (isset($res["stat"]) 
                && ($res["stat"] == 0 
                || $res["stat"] == 40004 
                || $res["stat"] == 40003) 
            )
            {
                break;
                //return $res;
            }
            //echo $buff."\n";
            usleep (200000);
        }
        return $res;
    } 
}

