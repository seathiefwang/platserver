<?php


require_once (__DIR__."/../libs/Error.php");
require_once (__DIR__."/../libs/Action.php");
/**
 * 情景模式管理类
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
class _Scene
{

    public $m_Error;

	public function __construct() {}

    public function __destruct() {}

    /**
     * 创建情景模式
     * 
     * @param params    params
     */
    public static function _Create($params)
    {
		if (isset($params['name'])
			&& isset($params['invokes'])
			&& is_array($params['invokes'])
		) {
			$name = $params['name'];
			$db = Db::init();
			$email = $_GET['u'];
			$mid = $_GET['mid'];
			$user = $db->get_var("select name from user where email=\"$email\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效
			
			if($db->get_results('SELECT * FROM scene WHERE name="'.$name.'" and mid="'.$mid.'";')) { 
				//情景模式已经存在
                return Error::getRetString(31002);
            }
			
			$db->query('INSERT INTO scene (name, mid) VALUES ("'.$name.'", "'.$mid.'")');
            $scene_id = $db->get_var('select sceneid from scene where name="'.$name.'" and mid="'.$mid.'"');
			
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
                    $did = strtoupper($cmdline["params"]["mid"]);
                    $mid = $db->get_var("select mid from mdevice where mid=\"$mid\"");
                    $db->query('INSERT INTO scene_info(`sceneid`,`order`,`cmdline`,`mid`,`did`) VALUES ('.$scene_id.','.$no.',\''.json_encode($cmdline).'\',\''.$mid.'\',\''.$did.'\')');
                }
                else
                {
                    return Error::getRetString(31004);//情景模式invoke列表格式错误。
                }
            }
            return Error::getRetString(0,array("sceneid"=>$scene_id)); //成功
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
        if (isset($params["sceneid"])
        ){
            $db = Db::init();
            $uuid=$_GET["u"];
            $mid=$_GET["mid"];
            $user = $db->get_var("select name from user where email=\"$uuid\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效

            $sceneid = $params["sceneid"];

            if ($db->query(<<<EOD
delete from scene where mid="$mid" and sceneid="$sceneid"
EOD
        ))
            {//成功
                $db->query(<<<EOD
delete from scene_info  where sceneid="$sceneid"
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
     * 获得情景模式信息
     * 
     * @param params    params
     */
    public static function allDeviceInitInvokes($mid)
    {
        require_once (__DIR__."/_Device.php");

            $all_dev = _Device::listByHouse($mid);

            $db = Db::init();

            $results = $all_dev["devices"]; 
            $scenes = array();
            if (isset($results[0]))
            {
                foreach ($results as $rec)
                {
                    $scenes[] = array(
                        "class"=>$rec["type"],
                        "Method"=>"TurnOff",
                        "params"=>array(
                            "name"=>$rec["name"],
                            "mid"=>$rec["did"],
                            "no"=>1,
                            )
                        );
                }
            }
            return $scenes;
    }
    /**
     * 获得情景模式列表
     * 
     * @param params    params
     */
    public static function getAllInfo($houseid)
    {

        $db = Db::init();

        $results = $db->get_results('select * from scene where mid="'.$mid.'" order by sceneid ');
        $scenes = array();
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $scenes[]=array("sceneid"=>$rec->sceneid*1, "name"=>$rec->name);
            }
        }
        return Error::getRetString(0, array("scenes"=>$scenes));
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
        $mid=$_GET["mid"];

        $user = $db->get_var("select name from user where email=\"$uuid\"");
        if (!isset($user)) return Error::getRetString(10009);//uuid无效

        $results = $db->get_results('select * from scene where mid="'.$mid.'" order by sceneid ');
        $scenes = array();
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $scenes[]=array("sceneid"=>$rec->sceneid*1, "name"=>$rec->name);
            }
        }
        //else
        //{
            //$scenes = self::getAllInfo($houseid);
        //}
        return Error::getRetString(0, array("scenes"=>$scenes));
    }

    /**
     * 获得情景模式信息
     * 
     * @param params    params
     */
    public static function _GetInfo($params)
    {
        if (isset($params["sceneid"]))
        {
            $sceneid = $params["sceneid"];
            $mid=$_GET["mid"];

            $db = Db::init();
            $uuid=$_GET["u"];
            $user = $db->get_var("select name from user where email=\"$uuid\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效

            $results = $db->get_results('select * from scene_info where sceneid='.$sceneid.' order by "order"');
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
                $scenes = self::allDeviceInitInvokes($mid);
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
            $uuid=$_GET["u"];
            $user = $db->get_var("select name from user where email=\"$uuid\"");
            if (!isset($user)) return Error::getRetString(10009);//uuid无效


            $results = $db->get_results('SELECT * FROM scene WHERE sceneid="'.$sceneid.'" ');
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
                    &&isset($cmdline["params"]["did"])
                )
                {//?????????????????????????????????????????????
                    $lmid = strtoupper($cmdline["params"]["did"]);
                     $mid = $db->get_var("select mid from mdevice where mid=\"$mid\"");
                    $db->query('INSERT INTO scene_info(`sceneid`,`order`,`cmdline`,`mid`,`did`) VALUES ('.$scene_id.','.$no.',\''.json_encode($cmdline).'\',\''.$mid.'\',\''.$did.'\')');
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
        if (isset($params["sceneid"])
        )
        {
        $sceneid = $params["sceneid"];
        $db=Db::init();
        $invokes = $db->get_results("select * from scene_info where sceneid=$sceneid");
        if(isset($invokes[0]))
        {
            foreach($invokes as $invoke)
            {
                //var_dump($invoke);
                Action::run($invoke->cmdline, $invoke->mid, $_GET["u"]);              
            }
        }
            return Error::getRetString(0);
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

}
?>
