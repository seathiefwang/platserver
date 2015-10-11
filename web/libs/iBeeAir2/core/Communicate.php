<?php
require_once (__DIR__."/../core/Error.php");
include_once __DIR__.("/../../Db.php");


/**
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:11:02
 */
class Communicate
{

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * 
     * @param params    params
     */
    protected static function sendToDevice($cont, $uuid, $udid, $tdid)
    {
        set_time_limit(0);  
        $buff = "";
        $host = "127.0.0.1";  
        $port = 2017;  
        $udid = strtoupper($udid);

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); // 创建一个Socket  

        $connection = socket_connect($socket, $host, $port);    //  连接  

        if ($connection)
        {
            socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>5, "usec"=>0 ) ); //读超时
            socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,array("sec"=>5, "usec"=>0 ) ); //发送超时
            socket_write($socket, $cont); // 数据传送 向服务器发送消息  
            $buff = socket_read($socket, 1024, PHP_BINARY_READ);  
            if ($buff == "")
            {
            }
            else
            {
                $json = json_decode($buff, true);
                //{"stat":0,"text":"ok","data":{"code":20001,"msg":"device no login"}}
                if (isset($json["stat"]) 
                    && isset($json["text"])
                    && isset($json["data"])
                )//验证json数据格式是否正确
                {
                    $data =  $json["data"];
                    if ($json["stat"]==0)
                    {
                        if ($data["code"]==0)//发送成功
                        {
                            //接收第一条数据
                            $buff = socket_read($socket, 1024, PHP_BINARY_READ);  
                            if ($buff=="")
                            {//主机没回复
                                return json_encode(
                                    array("code"=>11002,"msg"=>"device online but no reply")
                                );
                            }
                            else//收到主机第一条数据,准备接受第二条
                            {
                                socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>100, "usec"=>0 ) ); //读超时
                                $buff = socket_read($socket, 8196, PHP_BINARY_READ);  
                                if ($buff=="")
                                {//主机没回复,超时
                                    return json_encode(
                                        array("code"=>20003,"msg"=>"recv timeout")
                                    );
                                }
                                else//收到主机回复
                                {
                                    $json = json_decode($buff, true);
                                    if (isset($json["from"])  //验证主机回复数据
                                        && isset($json["msg"]))
                                    {
                                        if ($json["from"]!=$udid)
                                        { //数据异常
                                            return json_encode(
                                                array("code"=>11003,"msg"=>"data exception")
                                            );
                                        }

                                        $json_ret = json_encode(
                                            array("code"=>0, "msg"=>"ok", "result"=>$json["msg"])
                                        );
                                        return preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $json_ret);
                                    }
                                }
                            }
                        }
                        else
                        {//不成功
                            return json_encode(
                                array("code"=>$data["code"],"msg"=>$data["msg"])
                            );
                        }
                    }
                }
                else
                {
                    //服务器故障
                    return json_encode(
                        array("code"=>-1,"msg"=>"error")
                    );
                }
            }
        }
        return $buff;
    } 

}
?>
