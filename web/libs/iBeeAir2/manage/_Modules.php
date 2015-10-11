<?php


require_once (__DIR__."/../core/Error.php");
/**
 * 受控模块管理类
 * @author 律鑫
 * @version 1.0
 * @updated 09-六月-2015 16:04:59
 */
class _Modules
{

    public $m_Error;

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * 增加主机
     * 
     * @param params    params
     */
    public static function _Add($params)
    {
        if (is_array($params)
            && isset($params["devclass"])
            && isset($params["mid"])
            && isset($params["udid"])
            && isset($params["roomid"])
            && isset($params["name"])
            && is_array($params["name"])
        )
        {
            $devclass =$params["devclass"];
            $mid      =$params["mid"];     
            $udid     =$params["udid"];    
            $roomid   =$params["roomid"];  
            $names    =$params["name"];    

            //验证names数组是否合法
            if (isset($names[0]))
            {
                foreach ($names as $name)
                {
                    if(!isset($name["no"]) || !isset($name["name"]))
                        return Error::getRetString(10007);
                }
            }
            else
            {
                return Error::getRetString(10007);
            }


            $uuid=$_GET["uuid"];

            $db = Db::init();
            //模块是否已经被绑定
            if ($db->query(<<<EOD
select udid from device_modules where mid="$mid"
EOD
        ))
            {
                return Error::getRetString(10040);
            }

            //执行模块绑定
            if($db->query(<<<EOD
INSERT INTO device_modules(udid, mid, type, roomid) SELECT '$udid', '$mid','$devclass', '$roomid' FROM DUAL 
WHERE EXISTS(SELECT user FROM user WHERE uuid="$uuid" and priv=3)
 AND EXISTS(SELECT udid FROM user_device WHERE udid="$udid")
 AND EXISTS(SELECT roomid FROM user_room WHERE roomid="$roomid")
EOD
        ))
            {//主记录成功
                //保存子模块信息
                foreach ($names as $name)
                {
                    $na = $name["name"];
                    $no = $name["no"]; 
                    $db->query(<<<EOD
INSERT INTO device_modules_child(mid, name, no) SELECT '$mid', '$na', '$no' FROM DUAL
EOD
                );
                }
                return Error::getRetString(0);
            }
            else
            {
                return Error::getRetString(10016);
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
    public static function _Del($params)
    {
        if (is_array($params)
            && isset($params["mid"])
        )
        {
            $mid = $params["mid"];
            $uuid=$_GET["uuid"];

            $db = Db::init();

            //执行模块解绑
            if($db->query(<<<EOD
delete from device_modules WHERE EXISTS(SELECT user FROM user WHERE uuid="$uuid" and priv=3) and mid="$mid";
EOD
        ))
            {
                //删除子设备
                $db->query("delete from device_modules_child where mid=\"$mid\"");
                return Error::getRetString(0);
            }
            else
            {
                return Error::getRetString(10016);
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
    public static function _ListByRoom($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
        )
        {
            $roomid = $params["roomid"];
            $db = Db::init();

            $list = array("devices"=>array());
            $modules = $db->get_results("select * from device_modules where roomid=$roomid");
            //有模块
            if (isset($modules[0]))
            {
                foreach ($modules as $mod)
                {
                    $ch_info = array();
                    $m = $mod->mid;
                    $children = $db->get_results("select * from device_modules_child where mid=\"$m\" order by no");
                    foreach($children as $child)
                    {
                        $ch_name = $child->name;
                        $ch_no = $child->no*1;
                        $ch_info[] = array("no"=>$ch_no, "name"=>$ch_name);
                    }
                    $list["devices"][] = array(
                        "udid"=>$mod->udid,
                        "mid"=>$mod->mid,
                        "type"=>$mod->type,
                        "name"=>$ch_info
                    );
                }
            }
            return Error::getRetString(0, $list);
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
    public static function _ListByType($params)
    {
        if (is_array($params)
            && isset($params["devclass"])
        )
        {
            $type = $params["devclass"];
            $db = Db::init();

            $list = array("devices"=>array());
            $modules = $db->get_results("select * from device_modules where type=\"$type\"");
            //var_dump($modules);

            //有模块
            if (isset($modules[0]))
            {
                foreach ($modules as $mod)
                {
                    $ch_info = array();
                    $m = $mod->mid;
                    $children = $db->get_results("select * from device_modules_child where mid=\"$m\" order by no");
                    foreach($children as $child)
                    {
                        $ch_name = $child->name;
                        $ch_no = $child->no*1;
                        $ch_info[] = array("no"=>$ch_no, "name"=>$ch_name);
                    }
                    $list["devices"][] = array(
                        "udid"=>$mod->udid,
                        "mid"=>$mod->mid,
                        "roomid"=>$mod->roomid*1,
                        "name"=>$ch_info
                    );
                }
            }
            return Error::getRetString(0, $list);
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
    public static function _ListAll($params)
    {
        $uuid=$_GET["uuid"];
        $db = Db::init();
        $user = $db->get_var("select user from user where uuid=\"$uuid\"");
        if (!isset($user)) return Error::getRetString(10009);//uuid无效

        $re = array("rooms"=>array());
        $results = $db->get_results("select roomid,name from user_room where user=\"$user\"");
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $roomid = $rec->roomid*1;

                $list = array();
                $modules = $db->get_results("select * from device_modules where roomid=$roomid");
                //有模块
                if (isset($modules[0]))
                {
                    foreach ($modules as $mod)
                    {
                        $ch_info = array();
                        $m = $mod->mid;
                        $children = $db->get_results("select * from device_modules_child where mid=\"$m\" order by no");
                        foreach($children as $child)
                        {
                            $ch_name = $child->name;
                            $ch_no = $child->no*1;
                            $ch_info[] = array("no"=>$ch_no, "name"=>$ch_name);
                        }
                        $list[] = array(
                            "udid"=>$mod->udid,
                            "mid"=>$mod->mid,
                            "devclass"=>$mod->type,
                            "name"=>$ch_info
                        );
                    }
                }

                $re["rooms"][]=array("roomid"=>$roomid, "name"=>$rec->name, "devices"=>$list);
            }

        }
        return Error::getRetString(0, $re);
    }

}
?>
