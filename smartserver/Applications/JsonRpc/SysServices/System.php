<?php
use \GatewayWorker\Lib\Db;
use \GatewayWorker\Lib\Gateway;
/**
 *  测试
 * @author walkor <worker-man@qq.com>
 */
class System
{
    private static function save_log_stat($client_id, $uuid)  //保存连接参数
    {

    }

    public static function sendto($client_id, $uid)
    {
        //var_dump($uid);

        if (array_key_exists("udid", $uid)
            && array_key_exists("msg", $uid)
            && array_key_exists("uuid", $uid)
            && array_key_exists("tdid", $uid)
            && array_key_exists("user", $uid)
            && array_key_exists("synckey", $uid)
        )
        {
            $udid = $uid["udid"];
            $msg = $uid["msg"];
            $uuid = $uid["uuid"];
            $tdid = $uid["tdid"];
            $synckey = $uid["synckey"];
            $user = $uid["user"];
 

            $db = Db::instance('user'); 

            //$user = $db->query("select user from user where uuid='$uuid'");
            //if(!isset($user[0]))
            //{ //uuid无效
                //return array("code"=>11004, "msg"=>"username error");
            //}

            $db->query ("INSERT INTO `device_login`(`client_id`, `uuid`, `synckey`) VALUES ($client_id,\"$uuid\",\"$synckey\")");

            $devlist = $db->query ("select `client_id` from `device_login` where `udid`=\"$udid\"");
            if(!isset($devlist[0]))
            { //主机没有登录
                return array("code"=>20001, "msg"=>"device no login");
            }
            else
            {
                $cli_id = $devlist[0]["client_id"];
                if(Gateway::isOnline($cli_id))
                {
                    Gateway::sendToClient($cli_id, array(
                        'method'=>"whisp",
                        'params'=>array(
                        'tdid'=>$tdid,
                        'src'=>$user,
                        'from'=>$uuid,
                        'synckey'=>$synckey,
                        'msg'=>$msg)
                    ));
                }
                else
                {//主机离线
                    Gateway::closeClient ($cli_id);
                    return array("code"=>20002, "msg"=>"device offline");
                }
            }
            //发送成功
            return array("code"=>0, "msg"=>"ok");
        }
        else
        {   //命令参数不完整
            return array("code"=>10007, "msg"=>"params error");
        }
    }

    public static function bind($client_id, $uid)
    {
        //var_dump($uid);

        if (array_key_exists("udid", $uid)
            && array_key_exists("uuid", $uid)
            && array_key_exists("tdid", $uid)
            && array_key_exists("synckey", $uid)
        )
        {
            $udid = $uid["udid"];
            $uuid = $uid["uuid"];
            $tdid = $uid["tdid"];
            $synckey = $uid["synckey"];
 

            $db = Db::instance('user'); //测试数据库操作

            $user = $db->query("select user from user where uuid='$uuid'");
            if(!isset($user[0]))
            { //主机没有登录
                return array("code"=>11004, "msg"=>"username error");
            }

            $db->query ("INSERT INTO `device_login`(`client_id`, `uuid`, `synckey`) VALUES ($client_id,\"$uuid\",\"$synckey\")");

            $devlist = $db->query ("select `client_id` from `device_login` where `udid`=\"$udid\"");
            if(!isset($devlist[0]))
            { //主机没有登录
                return array("code"=>20001, "msg"=>"device no login");
            }
            else
            {
                $cli_id = $devlist[0]["client_id"];
                if(Gateway::isOnline($cli_id))
                {
                    Gateway::sendToClient(
                        $cli_id, array(
                            'method'=>"bind",
                            'params'=>array(
                                'tdid'=>$tdid,
                                'src'=>$user[0]["user"],
                                'from'=>$uuid,
                                'synckey'=>$synckey,
                                'msg'=>json_encode(array(
                                    'class'=>'Root',
                                    'method'=>'UserBind',
                                    'params'=>array(
                                        'user'=>$user[0]["user"]
                                    )
                                )
                            )
                        )
                    )
                );
                }
                else
                {//主机离线
                    Gateway::closeClient ($cli_id);
                    return array("code"=>20002, "msg"=>"device offline");
                }
            }
            //发送成功
            return array("code"=>0, "msg"=>"ok");
        }
        else
        {   //命令参数不完整
            return array("code"=>10007, "msg"=>"params error");
        }
    }


    public static function manageDevice($client_id, $uid)
    {
        //var_dump($uid);

        if (array_key_exists("udid", $uid)
            && array_key_exists("msg", $uid)
            && array_key_exists("uuid", $uid)
            && array_key_exists("tdid", $uid)
            && array_key_exists("synckey", $uid)
        )
        {
            $udid = $uid["udid"];
            $uuid = $uid["uuid"];
            $tdid = $uid["tdid"];
            $msg = $uid["msg"];
            $synckey = $uid["synckey"];
 

            $db = Db::instance('user'); //测试数据库操作

            //$user = $db->query("select user from user where uuid='$uuid'");
            //if(!isset($user[0]))
            //{ //uuid无效
                //return array("code"=>11004, "msg"=>"username error");
            //}

            $db->query ("INSERT INTO `device_login`(`client_id`, `uuid`, `synckey`) VALUES ($client_id,\"$uuid\",\"$synckey\")");

            $devlist = $db->query ("select `client_id` from `device_login` where `udid`=\"$udid\"");
            if(!isset($devlist[0]))
            { //主机没有登录
                return array("code"=>20001, "msg"=>"device no login");
            }
            else
            {
                $cli_id = $devlist[0]["client_id"];
                if(Gateway::isOnline($cli_id))
                {
                    Gateway::sendToClient(
                        $cli_id, array(
                            'method'=>"manage",
                            'params'=>array(
                                'tdid'=>$tdid,
                                'src'=>"server",
                                'from'=>$uuid,
                                'synckey'=>$synckey,
                                'msg'=>$msg
                                    )
                                )
                );
                }
                else
                {//主机离线
                    Gateway::closeClient ($cli_id);
                    return array("code"=>20002, "msg"=>"device offline");
                }
            }
            //发送成功
            return array("code"=>0, "msg"=>"ok");
        }
        else
        {   //命令参数不完整
            return array("code"=>10007, "msg"=>"params error");
        }
    }

    public static function getInfoByUid($client_id, $uid)
    {
        $_SESSION['UID'] = "ABC".$client_id."uid";
        return array(
            'uid'    => $uid,
            'name'=> 'test',
            'age'   => 18,
            'sex'    => 'hmm..',
        );
    }

    public static function getEmail($client_id, $uid)
    {
        $db = Db::instance('user'); //测试数据库操作
        var_dump($db->row("SELECT * FROM `net`"));
        return 'worker-man@qq.com';
    }
}
