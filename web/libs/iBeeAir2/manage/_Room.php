<?php


require_once (__DIR__."/../core/Error.php");
include_once __DIR__.("/../../Db.php");
/**
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
class _Room
{

    public $m_Error;

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
    public static function _Add($params)
    {
        if (is_array($params)
            && isset($params["user"])
            && isset($params["name"])
            && isset($params["icon"])
        )
        {
            $user = $params["user"]; 
            $name = $params["name"]; 
            $icon = $params["icon"]; 
            $uuid=$_GET["uuid"];

            $db = Db::init();
            //是否已存在
            if ($db->query(<<<EOD
select roomid from user_room where user="$user" and name="$name"
EOD
        ))
            {
                return Error::getRetString(10020);
            }

            $db->query(<<<EOD
INSERT INTO user_room(user, name, icon) SELECT '$user', '$name', '$icon' FROM DUAL WHERE EXISTS(SELECT user FROM user WHERE uuid="$uuid" and user="$user");
EOD
        );
            return Error::getRetString(0, array("roomid"=>$db->get_var("select roomid from user_room where user=\"$user\" and name=\"$name\"")));
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    /**
     * 
     * @param params    params
     */
    public static function _Del($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
        )
        {
            $uuid=$_GET["uuid"];
            $roomid = $params["roomid"];

            $db = Db::init();

            $user = $db->get_var("select user from user where uuid=\"$uuid\"");

            if (!isset($user)) return Error::getRetString(10009);//uuid无效

            //执行删除操作
            if ($db->query(<<<EOD
delete from user_room where user="$user" and roomid="$roomid"
EOD
        ))
            {//成功
                $room0id = $db->get_var("select room0id from user where user=\"$user\"");
                //把该区域的设备移到未分组设备中
                $db->query("update device_modules set roomid=$room0id where roomid=$roomid");
                return Error::getRetString(0);
            }
            else
            {//区域不存在
                return Error::getRetString(10021);
            }

        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    /**
     * 
     * @param params    params
     */
    public static function _Modify($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
            && isset($params["user"])
            && isset($params["name"])
            && isset($params["icon"])
        )
        {
            $uuid=$_GET["uuid"];
            $roomid = $params["roomid"];
            $user = $params["user"];
            $name = $params["name"];
            $icon = $params["icon"]; 

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE user_room set name='$name', icon='$icon' WHERE EXISTS(SELECT user FROM user WHERE uuid="$uuid" and user="$user") and user='$user' and roomid='$roomid';
EOD
        ))
            {//成功
                return Error::getRetString(0);
            }
            else
            {//区域不存在
                return Error::getRetString(10021);
            }
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    /**
     * 
     * @param params    params
     */
    public static function _List($params)
    {
        $db = Db::init();
        $uuid=$_GET["uuid"];
        $user = $db->get_var("select user from user where uuid=\"$uuid\"");

        if (!isset($user)) 
            return Error::getRetString(10009);//uuid无效

        $re = array("rooms"=>array());
        $results = $db->get_results("select roomid,name,icon from user_room where user=\"$user\"");
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $re["rooms"][]=array(
                    "roomid"=>$rec->roomid*1, 
                    "icon"=>$rec->icon*1, 
                    "name"=>$rec->name
                );
            }
        }
        return Error::getRetString(0, $re);
    }

}
?>
