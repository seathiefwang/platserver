<?php


require_once (__DIR__."/../core/Error.php");
/**
 * 情景模式管理类
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
class _Assistant
{

    public $m_Error;

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * 创建情景模式
     * 
     * @param params    params
     */
    public static function _Create($params)
    {
        if (isset($params["time"])
            && is_array($params["invokes"])
        )
        {
            $name = $params["time"];
            $db = Db::init();
            $email=$_GET["u"];
            $houseid=$_GET["hid"];
            $user = $db->get_var("select name from user where email=\"$email\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效


            if($db->get_results('SELECT * FROM assistant WHERE time="'.$name.'" and houseid="'.$houseid.'";'))
            { //情景模式已经存在
                return Error::getRetString(31002);//情景模式名称已存在
            }

            $db->query('INSERT INTO assistant (time, houseid) VALUES ("'.$name.'", "'.$houseid.'")');
            $scene_id = $db->get_var('select assid from assistant where time="'.$name.'" and houseid="'.$houseid.'"');

            //保存情景模式命令行
            $invokes = $params["invokes"];
            foreach ($invokes as $no=>$cmdline)
            {//处理命令序列
                if (isset($cmdline["class"])
                    &&isset($cmdline["method"])
                    &&isset($cmdline["params"])
                    &&isset($cmdline["params"]["mid"])
                )
                {
                    $lmid = strtoupper($cmdline["params"]["mid"]);
                    $udid = $db->get_var("select mid from house where houseid=\"$houseid\"");
                    $db->query('INSERT INTO assi_info(`assid`,`order`,`cmdline`,`mid`) VALUES ('.$scene_id.','.$no.',\''.json_encode($cmdline).'\',\''.strtoupper($cmdline["params"]["mid"]).'\')');
                }
                else
                {
                    return Error::getRetString(31004);//情景模式invoke列表格式错误。
                }
            }
            return Error::getRetString(0,array("assid"=>$scene_id)); //成功
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    /**
     * 删除情景模式
     * 
     * @param params    params
     */
    public static function _Destroy($params)
    {
        if (isset($params["assid"])
        )
        {
            $db = Db::init();
            $uuid=$_GET["u"];
            $houseid=$_GET["hid"];
            $user = $db->get_var("select name from user where email=\"$uuid\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效

            $sceneid = $params["assid"];

            if ($db->query(<<<EOD
delete from assistant where houseid="$houseid" and assid="$sceneid"
EOD
        ))
            {//成功
                $db->query(<<<EOD
delete from assi_info  where assid="$sceneid"
EOD
            );
                return Error::getRetString(0);
            }
            else
            {//情景模式不存在
                return Error::getRetString(31001);
            }

        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    /**
     * 获得情景模式列表
     * 
     * @param params    params
     */
    public static function _List($params)
    {

        $db = Db::init();
        $uuid=$_GET["u"];
        $houseid=$_GET["hid"];

        $user = $db->get_var("select name from user where email=\"$uuid\"");
        if (!isset($user)) return Error::getRetString(10009);//uuid无效

        $results = $db->get_results('select * from assistant where houseid="'.$houseid.'" order by assid ');
        $scenes = array();
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $scenes[]=array("assid"=>$rec->assid*1, "time"=>$rec->time);
            }
        }
        return Error::getRetString(0, array("assis"=>$scenes));
    }

    /**
     * 获得情景模式信息
     * 
     * @param params    params
     */
    public static function _GetInfo($params)
    {
        if (isset($params["assid"]))
        {
            $sceneid = $params["assid"];

            $db = Db::init();
            $uuid=$_GET["u"];
            $houseid=$_GET["hid"];
            $user = $db->get_var("select name from user where email=\"$uuid\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效

            $results = $db->get_results('select * from assi_info where assid='.$sceneid.' order by "order"');
            $scenes = array();
            if (isset($results[0]))
            {
                foreach ($results as $rec)
                {
                    $scenes[]=json_decode($rec->cmdline);
                }
            }
            else
            {
                require_once (__DIR__."/_Scene.php");
               $scenes = _Scene::allDeviceInitInvokes($houseid); 
            }
            return Error::getRetString(0, array("invokes"=>$scenes));
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    /**
     * 修改情景模式
     * 
     * @param params    params
     */
    public static function _Modify($params)
    {
        if (isset($params["name"])
            && isset($params["sceneid"]) 
            && is_array($params["invokes"])
        )
        {
            $name = $params["name"];
            $sceneid = $params["sceneid"];
            $db = Db::init();
            $uuid=$_GET["uuid"];
            $user = $db->get_var("select user from user where uuid=\"$uuid\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效


            $results = $db->get_results('SELECT * FROM scene WHERE sceneid="'.$sceneid.'" and user="'.$user.'"');
            if(!isset($results[0]))
            { //情景模式不存在
                return Error::getRetString(31001);//情景模式名称不存在
            }

            $db->query('UPDATE scene SET `name`=\''.$name.'\' WHERE `sceneid`='.$sceneid);

            //$db->query('INSERT INTO scene (name, user) VALUES ("'.$name.'", "'.$user.'")');
            //$scene_id = $db->get_var('select sceneid from scene where name="'.$name.'" and user="'.$user.'"');
            $db->query("delete from scene_info where sceneid=$sceneid;");

            //保存情景模式命令行
            $invokes = $params["invokes"];
            foreach ($invokes as $no=>$cmdline)
            {//处理命令序列
                if (isset($cmdline["class"])
                    &&isset($cmdline["method"])
                    &&isset($cmdline["params"])
                    &&isset($cmdline["params"]["mid"])
                )
                {
                    $lmid = strtoupper($cmdline["params"]["mid"]);
                    $udid = $db->get_var("select udid from device_modules where mid=\"$lmid\"");
                    $db->query('INSERT INTO scene_info(`sceneid`,`order`,`cmdline`,`mid`,`udid`) VALUES ('.$sceneid.','.$no.',\''.json_encode($cmdline).'\',\''.strtoupper($cmdline["params"]["mid"]).'\', \''.$udid.'\')');
                }
                else
                {
                    return Error::getRetString(31004);//情景模式invoke列表格式错误。
                }
            }
            return Error::getRetString(0,array("sceneid"=>$sceneid)); //成功
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    /**
     * 执行情景模式
     * 
     * @param params    params
     */
    public static function _Excute($params)
    {
            return Error::getRetString(0);
    }

}
?>
