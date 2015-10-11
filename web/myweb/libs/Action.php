<?php
require_once (__DIR__."/Event.php");
require_once (__DIR__."/DeviceControl.php");
require_once (__DIR__."/Protocol.php");
require_once (__DIR__."/../db/Db.php");
require_once (__DIR__."/../base/_Notify.php");

class Action
{
    public static function run($string, $mid, $email)
    {
        $ret = DeviceControl::sendTo($string, $email, $mid, $email);
        $array = Protocol::decode($ret);
        if(isset($array["result"]))
        {
            $ret_string = ($array["result"]);

            $data = $array["result"];

            $json = json_decode($string, true);
            $did = $json["params"]["mid"];
            $db = Db::init();
			$var = $db->get_var("select notify from device where did='$did'");
	
            switch($json["method"])
            {
            case "TurnOn":
				if($var == 1)
					_Notify::add($mid, $did, $email, "开启");
                $stat = 1;
                break;
            case "TurnOff":
				if($var == 1)
					_Notify::add($mid, $did, $email, "关闭");
                $stat = 0;
                break;
            default:
                $stat = 0;
            }
            $db->query("update device set stat=$stat where did='$did'");
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
