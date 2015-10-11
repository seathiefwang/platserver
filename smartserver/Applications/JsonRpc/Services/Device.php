<?php
use \GatewayWorker\Lib\Db;
use \GatewayWorker\Lib\Gateway;
/**
 *  测试
 * @author walkor <worker-man@qq.com>
 */
class Device
{
    public static function Login($client_id, $uid)
    {
        //var_dump($uid);

        if (array_key_exists("udid", $uid))
        {
            $udid = $uid["udid"];
            echo $udid;
            //验证是否已经登录
            if (!empty($_SESSION['UID']))
            {
                echo $client_id."已经登录\n";
            }
            else//处理登录逻辑
            {
                $_SESSION['UID'] = "ABC".$client_id."uid";
                $db = Db::instance('user'); //测试数据库操作

                //关闭同一个uuid设备的其他连接,保持唯一性
                $devlist = $db->query ("select `client_id` from `device_login` where `udid`=\"$udid\"");
                if (isset($devlist[0]))
                {
                    foreach ($devlist as $dev)
                    {
                        $dest_cli_id = $dev["client_id"];
                        if ($client_id != $dest_cli_id)
                        {
                            Gateway::closeClient($dest_cli_id);
                            $db->query ("DELETE FROM `device_login` WHERE `udid` = \"$udid\"");
                            $db->query ("INSERT INTO `device_login`(`client_id`, `udid`) VALUES ($client_id,\"$udid\")");
                        }
                    }
                }
                else
                {
                    $db->query ("INSERT INTO `device_login`(`client_id`, `udid`) VALUES ($client_id,\"$udid\")");
                }
            }
            return array(
                'code'    => 20000,
                'msg'=> "ok",
            );
        }
        else
        {
            //参数不完整
            return array(
                'code'    => 10007,
                'msg'=> "error",
            );
        }
    }

    //回复由客户端发来的消息
    public static function oldreply($client_id, $uid)
    {
        if (array_key_exists("uuid", $uid)
            && array_key_exists("msg", $uid)
            && array_key_exists("udid", $uid)
        )
        {
            $udid = $uid["udid"];
            $msg = $uid["msg"];
            $uuid = $uid["uuid"];

            $db = Db::instance('user'); //测试数据库操作

            $devlist = $db->query ("select `client_id` from `device_login` where `uuid`=\'$uuid\'");
            if (isset($devlist[0]))
            {
                foreach ($devlist as $dev)
                {
                    $dest_cli_id = $dev["client_id"];
                    Gateway::sendToClient($dest_cli_id, array(
                        'from'=>$udid,
                        'msg'=>preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $msg),
                    )
                );
                    //$db->query ("DELETE FROM `device_login` WHERE `client_id` = $dest_cli_id");
                }
                //执行成功
                return array("code"=>0, "msg"=>"ok");
            }
            else
            {
                //回复超时
                return array("code"=>20003, "msg"=>"timeout");
            }
        }
        //参数不完整
        return array(
            'code'    => 10007,
            'msg'=> "error",
        );
    }

    //回复由客户端发来的消息
    public static function reply($client_id, $uid)
    {
        if (array_key_exists("to", $uid)
            && array_key_exists("msg", $uid)
            && array_key_exists("udid", $uid)
            && array_key_exists("synckey", $uid)
        )
        {
            $udid = $uid["udid"];
            $msg = $uid["msg"];
            $uuid = $uid["to"];
            $synckey = $uid["synckey"];

            $db = Db::instance('user'); //测试数据库操作

            $devlist = $db->query ("select `client_id` from `device_login` where  `synckey`=\"$synckey\"");
            if (isset($devlist[0]))
            {
                foreach ($devlist as $dev)
                {
                    $dest_cli_id = $dev["client_id"];
                    Gateway::sendToClient($dest_cli_id, array(
                        'from'=>$udid,
                        'msg'=>preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $msg),
                    )
                );
                    //$db->query ("DELETE FROM `device_login` WHERE `client_id` = $dest_cli_id");
                }
                //执行成功
                return array("code"=>0, "msg"=>"ok");
            }
            else
            {
                //回复超时
                return array("code"=>20003, "msg"=>"timeout");
            }
        }
        //参数不完整
        return array(
            'code'    => 10007,
            'msg'=> "error",
        );
    }

    public static function Alive($client_id, $uid)
    {}
}
