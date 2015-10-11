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
            && isset($params["houseid"])
            && isset($params["name"])
        )
        {
            $name = $params["name"]; 
            $houseid = $params["houseid"]; 
            $email=$_GET["u"];

            $db = Db::init();
            //是否已存在
            if ($db->query(<<<EOD
select roomid from room where houseid=$houseid and name="$name"
EOD
        ))
            {
                return Error::getRetString(10020);
            }

            if($db->query(<<<EOD
INSERT INTO room(houseid, name, email) SELECT '$houseid','$name','$email' FROM DUAL WHERE EXISTS(select houseid from house where houseid=$houseid)
EOD
        ))
            {
                return Error::getRetString(0, array("roomid"=>$db->get_var("select roomid from room where houseid=$houseid and name=\"$name\"")));
            }
            else
            {
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
    public static function _Del($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
            && isset($params["houseid"])
        )
        {
            $email=$_GET["u"];
            $roomid = $params["roomid"];
            $houseid = $params["houseid"]; 

            $db = Db::init();

            //执行删除操作
            if ($db->query(<<<EOD
delete from room where houseid=$houseid and roomid="$roomid" and exists(select houseid from house where email="$email" and houseid=$houseid)
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
    public static function _Modify($params)
    {
        if (is_array($params)
            && isset($params["roomid"])
            && isset($params["houseid"])
            && isset($params["name"])
        )
        {
            $email=$_GET["u"];
            $roomid = $params["roomid"];
            $houseid = $params["houseid"];
            $name = $params["name"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE room set name='$name' WHERE EXISTS(select houseid from house where email="$email" and houseid=$houseid) and houseid='$houseid' and roomid='$roomid';
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
        if (is_array($params)
            && isset($params["houseid"])
        )
        {
            $db = Db::init();
            $email=$_GET["u"];
            $houseid = $params["houseid"];

            $re = array("rooms"=>array());
            $results = $db->get_results(<<<EOD
select roomid,name from room where houseid=$houseid and exists(select houseid from house where houseid=$houseid and email="$email") 
EOD
);
            if (isset($results[0]))
            {
                foreach ($results as $rec)
                {
                    $re["rooms"][]=array(
                        "roomid"=>$rec->roomid*1, 
                        "name"=>$rec->name
                    );
                }
            }
            return Error::getRetString(0, $re);
        }
        else
        {
            return Error::getRetString(10007);
        }
    }

}
?>
