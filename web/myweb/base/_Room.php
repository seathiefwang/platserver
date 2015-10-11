<?php


require_once (__DIR__."/../libs/Error.php");
include_once (__DIR__."/../db/Db.php");

class _Room
{

    public $m_Error;

	public function __construct() {}

    public function __destruct() {}

    /**
     * 
     * @param params    params
     */
    public static function _Add($params)
    {
        if (is_array($params)
            && isset($params["mid"])
            && isset($params["name"])
        ) {
            $name = $params["name"]; 
            $mid = $params["mid"]; 
            $email=$_GET["u"];

            $db = Db::init();
            //是否已存在
            if ($db->query(<<<EOD
select roomid from room where mid=$mid and name="$name"
EOD
        )) {
                return Error::getRetString(10020);
            }

            if($db->query(<<<EOD
INSERT INTO room(mid, name) SELECT '$mid','$name' FROM DUAL WHERE EXISTS(select mid from mdevice where mid='$mid')
EOD
        )) {
                return Error::getRetString(0, array("roomid"=>$db->get_var("select roomid from room where mid='$mid' and name='$name'")));
            } else {
                return Error::getRetString(10021);
            }
        } else {
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
        ) {
            $email=$_GET["u"];
            $roomid = $params["roomid"];
            $mid = $params["mid"]; 

            $db = Db::init();

            //执行删除操作
            if ($db->query(<<<EOD
delete from room where mid=$mid and roomid="$roomid" and exists(select mid from mdevice where mid='$mid')
EOD
        )) {//成功
				$db->query(<<<EOD
delete from device where roomid="$roomid"
EOD
				);
                return Error::getRetString(0);
            } else {//区域不存在
                return Error::getRetString(10021);
            }

        } else {
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
        ) {
            $email=$_GET["u"];
            $roomid = $params["roomid"];
            $mid = $params["mid"];
            $name = $params["name"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE room set name='$name' WHERE EXISTS(select mid from mdevice where mid="$mid") and mid='$mid' and roomid='$roomid';
EOD
        )) {//成功
                return Error::getRetString(0);
            } else {//区域不存在
                return Error::getRetString(10021);
            }
        } else {
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
            && isset($params["mid"])
			&& isset($params["vercode"])
        ) {
            $db = Db::init();
            $email=$_GET["u"];
            $mid = $params["mid"];
			$vercode = $params["vercode"];
			
            $re = array("rooms"=>array());
            $results = $db->get_results(<<<EOD
select roomid,name from room where mid='$mid' and exists(select mid from mdevice where mid='$mid' and vercode='$vercode') 
EOD
);
            if (isset($results[0])) {
                foreach ($results as $rec)
                {
                    $re["rooms"][]=array(
                        "roomid"=>$rec->roomid*1, 
                        "name"=>$rec->name
                    );
                }
            }
            return Error::getRetString(0, $re);
        } else {
            return Error::getRetString(10007);
        }
    }

}
?>