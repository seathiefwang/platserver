<?php


require_once (__DIR__."/../core/Error.php");
include_once __DIR__.("/../../Db.php");
/**
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
class _Notify
{

    public $m_Error;

	public function __construct() {}

    public function __destruct() {}

    /**
     * 
     * @param params    params
     */
    public static function _List($params)
    {
        $email = $_GET["u"];
        $db = Db::init();
        $notifies = array();
        $recs = $db->get_results("select mid,time from user_notify where email='$email'order by id DESC LIMIT 20 ");//id降序排列并取其中的20条记录
        if (isset($recs[0]))
        {
            foreach($recs as $mid)
            {
                $notifies[] = self::getInfo($mid->mid, $mid->time);
            }
        }
        return Error::getRetString(0, array("notifies"=>$notifies));
    }


    /**
     * 
     * @param params    params
     */
    public static function _ListMM($params)
    {
        return <<<EOD
{
    "code": 0,
    "msg": "ok",
    "results": {
        "notifies": [
            {
                "house": "别墅",
                "room": "客厅",
                "user": "爸爸",
                "time": "12:00",
                "msg": "空调伴侣开启"
            },
            {
                "house": "别墅",
                "room": "客厅",
                "user": "爸爸",
                "time": "13:00",
                "msg": "空调伴侣开启"
            },
            {
                "house": "别墅",
                "room": "客厅",
                "user": "爸爸",
                "time": "14:00",
                "msg": "空调伴侣开启"
            },
            {
                "house": "别墅",
                "room": "卧室",
                "user": "爸爸",
                "time": "11:20",
                "msg": "顶灯开启"
            }
        ]
    }
}
EOD;
    }

    public static function _Add($params)
    {
        self::add("13221133", $_GET["u"], "开启");
    }

    public static function add($mid, $email, $msg)
    {
        $db=Db::init();
        date_default_timezone_set("Asia/Chongqing");
        $name = $db->get_var("select name from device where mid='$mid'");
        $msg = $name." ".$msg;
        $time =  (string)date("H:i:s");
        return $db->query("insert into user_notify(mid, email, msg, time) select '$mid', '$email', '$msg', '$time' from dual");
    }

    private static function getInfo($mid, $time)
    {
        $msg = $house_n = $room_n = $user_n = "";
        // $email = $_GET["u"];
        $db=Db::init();
        if($room = $db->get_row("select roomid, name from device where mid='$mid'"))
        {
            $room_n = $room->name;
            if($house = $db->get_row("select houseid, name, email from room where roomid=$room->roomid"))
            {
                $user = $db->get_row("select name from room where houseid=$house->houseid");
                $msg = $db->get_row("select msg,time from user_notify where mid='$mid'");
                $house_n = $house->name;
                $user_n = $user->name;
            }
        }

        $arr = array(
            "house"=>((string) $house_n  ),
            "room" =>((string) $room_n   ),
            "user" =>((string) $user_n   ),
            "time" =>((string) $time ),
            "msg"  =>((string) isset($msg->msg)?$msg->msg:null  ),
        );
        return $arr;
        //return urldecode(json_encode($arr));
    }

    public static function _ListA($params)
    {
        if (isset($params["mid"]))
        {
            $mid=$params["mid"];
            return urldecode(json_encode(self::getInfo($mid)));
        }
    }
}
?>
