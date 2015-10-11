<?php
require_once (__DIR__."/Event.php");
require_once (__DIR__."/DeviceControl.php");
require_once (__DIR__."/Protocol.php");
require_once (__DIR__."/../../Db.php");
require_once (__DIR__."/../base/_Notify.php");

class Action
{
    public static function run($string, $udid, $email)
    {
        $ret = DeviceControl::sendTo($string, $email, $udid, $email);
        $array = Protocol::decode($ret);
        if(isset($array["result"]))
        {
            $ret_string = ($array["result"]);

            $data = $array["result"];

            $json = json_decode($string, true);
            $mid = $json["params"]["mid"];
            $db = Db::init();
            switch($json["method"])
            {
            case "TurnOn":
                _Notify::add($mid, $_GET["u"], "开启");
                $stat = 1;
                break;
            case "TurnOff":
                _Notify::add($mid, $_GET["u"], "关闭");
                $stat = 0;
                break;
            default:
                $stat = 0;
            }
            $db->query("update device set stat=$stat where mid='$mid'");
        }
        else if(isset($array["data"])) //主机返回数据
        {
            $ret_string = ($data);
        }
        else
        {
            $ret_string = $ret;
            //echo Error::getRetString(-1);
        }
        return $ret_string;
    }
}
