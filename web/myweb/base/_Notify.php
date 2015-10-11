<?php


require_once (__DIR__."/../libs/Error.php");
include_once (__DIR__."/../db/Db.php");
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
        if (isset($params["pageNO"])
            && isset($params["pageRecs"])
        ) {
            $email = $_GET["u"];
            $db = Db::init();
            $notifies = array();

			$pageTotal = 0;
			$total = 0;
            $page =  (int)$params["pageNO"];
            $limit = (int)$params["pageRecs"]; //限制每页条数

            $offset = $page * $limit;
			$resmid = $db->get_results("select mid from udevice where email='$email'");
			
			if (isset($resmid[0])) {
				$tmp = $resmid[0]->mid;
				$cmd = " mid='$tmp' ";
				for	($i=1; $i<count($resmid); $i++) {
					$tmp = $resmid[$i]->mid;
					$cmd = $cmd."or mid='$tmp' "; 
				}
				$recs = $db->get_results("select mid, did, email, time, msg from user_notify where $cmd order by id DESC LIMIT $offset,$limit ");
				$total = (int)$db->get_var("select count(*) from user_notify where $cmd order by id");		
				$pageTotal = ceil($total/$limit);
								
				if (isset($recs[0])) {
					foreach($recs as $rec)
					{
						$_info = self::getInfo($rec);
						if (isset($_info))
							$notifies[] =$_info; 
					}
				}			
					
			}						
			
            return Error::getRetString(0, array("notifies"=>$notifies, "pageTotal"=>$pageTotal, "pageRecs"=>$limit, "recsTotal"=>$total));
        } else {
            return Error::getRetString(10007);
        }
    }

	public static function getInfo($rec)
	{
		$db=Db::init();
		if($room = $db->get_row("select roomid, name from device where did='$rec->did'")) {
			
            $room_name = $db->get_var("select name from room where roomid=$room->roomid");
            $house = $db->get_var("select name from mdevice where mid='$rec->mid'");
			$user = $db->get_var("select name from user where email='$rec->email'");
			if(!isset($user)){
				$user = $rec->email;
			}
			$arr = array(
				"house"=>((string) $house  ),
				"room" =>((string) $room_name  ),
				"user" =>((string) $user  ),
				"time" =>((string) $rec->time ),
				"msg"  =>((string) isset($rec->msg)?$rec->msg:'null' ),
			);
			return $arr;
        } else 
            return null;
	}
	
    public static function _Add($params)
    {
        self::add("13221133", "12341234", $_GET["u"], "开启");
    }

    public static function add($mid, $did, $email, $msg)
    {
        $db=Db::init();
        date_default_timezone_set("Asia/Chongqing");
        $name = $db->get_var("select name from device where did='$did'");
        $msg = $name." ".$msg;
 //       $time =  (string)date("H:i:s");
		$time =  (string)date("m-d h:ia");
        return $db->query("insert into user_notify(mid, did, email, msg, time) select '$mid', '$did', '$email', '$msg', '$time' from dual");
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