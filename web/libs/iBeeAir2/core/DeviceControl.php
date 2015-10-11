<?php


require_once ("Communicate.php");
require_once ("Error.php");
require_once (__DIR__."/../../Db.php");
require_once (__DIR__."/../../User.php");
/**
 * 设备控制类
 * @author 律鑫
 * @version 1.0
 * @updated 09-六月-2015 11:20:02
 */
class DeviceControl extends Communicate
{

	public $m_Error;

	public function __construct()
	{
	}

	public function __destruct()
	{
	}

	/**
	 * 发送控制指令
	 * 
	 * @param params    params
	 */
	public static function SendTo($string, $uuid, $udid, $tdid)
	{

            $db=Db::init();

            //验证用户
            //$user = $db->get_var("select user from user where uuid=\"$uuid\"");
            //if (!isset($user)) return Error::getRetString(10009);//uuid无效
            $json["synckey"] = User::CreateUUID();
            $json["udid"] = $udid;
            $json["uuid"] = $uuid;
            $json["tdid"] = $tdid;
            $json["user"] = $uuid;
            $json["msg"] = $string;
            $arr = array(
                "class"=>"System",
                "method"=>"sendTo",
                "param_array"=>$json
            );

              return self::sendToDevice(json_encode($arr)."\n", $uuid, $udid, $uuid);
        }
	/**
	 * 发送控制指令
	 * 
	 * @param params    params
	 */
	protected static function control($params, $cmd)
	{
            if (!isset($params["mid"]))
                return Error::getRetString(10007);

            $mid = $params["mid"];
            $uuid = $_GET["uuid"];
            $udid = $_GET["udid"];
            $db=Db::init();

            //验证用户
            $user = $db->get_var("select user from user where uuid=\"$uuid\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效

            if($db->query(<<<EOD
select mid from device_modules where mid="$mid" and udid="$udid"
and exists(select udid from user_device where user="$user" and udid="$udid")
EOD
        ))
            {//本地验证成功，开始与设备进行通信
                $string = file_get_contents("php://input"); 
                return self::sendToDevice($string, $uuid, $udid, $uuid);
                return Error::getRetString(0);
            }
            else
            {
                return Error::getRetString(10016);
            }
            return Error::getRetString(0, array("uuid"=>$uuid, "udid"=>$udid, "cmd"=>$cmd));
	}

}
?>
