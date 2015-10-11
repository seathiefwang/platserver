<?php


require_once (__DIR__."/../core/Error.php");
/**
 * 受控模块管理类
 * @author 律鑫
 * @version 1.0
 * @updated 09-六月-2015 16:04:59
 */
class _Device
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
            && isset($params["roomid"])
            && isset($params["mid"])
            && isset($params["name"])
            && isset($params["type"])
        )
        {
            $roomid   =$params["roomid"];  
            $mid      =$params["mid"];     
            $name     =$params["name"];    
            $type     =$params["type"];

            $email=$_GET["u"];


            $db = Db::init();
            //模块是否已经被绑定
            if ($db->query(<<<EOD
select mid from device where mid="$mid"
EOD
        ))
            {
                return Error::getRetString(10040);
            }

            //执行模块绑定
            if($db->query(<<<EOD
INSERT INTO device(mid, name, type, roomid) SELECT '$mid', '$name','$type', '$roomid' FROM DUAL 
WHERE EXISTS(SELECT roomid FROM room WHERE roomid="$roomid" and email="$email")
EOD
        ))
            {//记录成功
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
            && isset($params["roomid"])
            && isset($params["mid"])
        )
        {
            $roomid = $params["roomid"];
            $mid = $params["mid"];
            $email=$_GET["u"];

            $db = Db::init();

            //执行删除操作
            if ($db->query(<<<EOD
delete from device where mid="$mid" and roomid=$roomid 
AND EXISTS(SELECT roomid FROM room WHERE roomid="$roomid" and email="$email")
EOD
        ))
            {//成功
                return Error::getRetString(0);
            }
            else
            {//不存在
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
            && isset($params["mid"])
            && isset($params["name"])
        )
        {
            $email=$_GET["u"];
            $roomid = $params["roomid"];
            $mid = $params["mid"];
            $name = $params["name"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE device set name='$name' 
WHERE EXISTS(SELECT roomid FROM room WHERE roomid="$roomid" and email="$email")
and mid='$mid' and roomid='$roomid';
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
    public static function _ListByHouse($params)
    {
        if (is_array($params)
            && isset($params["houseid"])
        )
        {
            $houseid = $params["houseid"];

            $re = self::listByHouse($houseid);
            return Error::getRetString(0, $re);
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

    public static function listByHouse($houseid)
    {

        $db = Db::init();
        $email=$_GET["u"];
        $re = array("devices"=>array());
        $results = $db->get_results(<<<EOD
select roomid from room where houseid=$houseid 
and exists(select houseid from house where houseid=$houseid and email="$email") 
EOD
    );
        if (isset($results[0]))
        {
            foreach ($results as $roomid)
            {
                $devs = self::listByRoom((int)$roomid->roomid);
                $re["devices"] = array_merge($re["devices"], $devs["devices"]);
            }
        }
        return $re;
    }
    public static function listByRoom($roomid)
    {
        $email=$_GET["u"];
        $db = Db::init();
        $re = array("devices"=>array());
        $results = $db->get_results(<<<EOD
select * from device where roomid=$roomid 
and exists(select roomid from house where roomid=$roomid and email="$email") 
EOD
    );
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $re["devices"][]=array(
                    "mid"=>$rec->mid, 
                    "name"=>$rec->name,
                    "type"=>$rec->type,
                    "stat"=>(int)$rec->stat,
                    "notify"=>($rec->notify*1)?true:false,
                    "assisEn"=>($rec->assisEn*1)?true:false,
                );
            }
        }
        return $re;
    }

    /**
     * 
     * @param params    params
     */
    public static function _List($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
        )
        {
            $roomid = $params["roomid"];

            $re = self::listByRoom($roomid);
            return Error::getRetString(0, $re);
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
    public static function _SetNotify($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
            && isset($params["mid"])
            && isset($params["notify"])
        )
        {
            $email=$_GET["u"];
            $roomid = $params["roomid"];
            $mid = $params["mid"];
            $notify = $params["notify"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE device set notify='$notify' 
WHERE EXISTS(SELECT roomid FROM room WHERE roomid="$roomid" and email="$email")
and mid='$mid' and roomid='$roomid';
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
    public static function _AssisEnable($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
            && isset($params["mid"])
            && isset($params["assisEn"])
        )
        {
            $email=$_GET["u"];
            $roomid = $params["roomid"];
            $mid = $params["mid"];
            $assisEn = $params["assisEn"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE device set assisEn='$assisEn' 
WHERE EXISTS(SELECT roomid FROM room WHERE roomid="$roomid" and email="$email")
and mid='$mid' and roomid='$roomid';
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

}
?>
