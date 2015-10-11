<?php


require_once (__DIR__."/../libs/Error.php");
include_once (__DIR__."/../db/Db.php");

class _AllDevice
{

    public $m_Error;

	public function __construct() {}

    public function __destruct() {}

	public static function _Add($params) 
	{
		if (is_array($params)
            && isset($params['mid'])
        ) {
            $name = $params["name"]; 
            $mid = $params["mid"]; 
			$vercode = $params['vercode'];
            $stat = $params['stat'];

            $db = Db::init();
            //是否已存在
            if ($db->query(<<<EOD
select mid from alldevice where mid=$mid
EOD
        )) {
                return Error::getRetString(10020);
            }

            if($db->query(<<<EOD
INSERT INTO alldevice(mid, vercode, stat) SELECT '$mid','$vercode', '$stat' FROM DUAL
EOD
        )) {
                return Error::getRetString(0);
            } else {
                return Error::getRetString(10021);
            }
        } else {
            return Error::getRetString(10007);
        }
	}
	
	public static function _Del($params) 
	{
		if (is_array($params)
            && isset($params["mid"])
        ) {
            $mid = $params["mid"]; 

            $db = Db::init();

            //执行删除操作
            if ($db->query(<<<EOD
delete from alldevice where mid=$mid
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
	
	public static function _Modify($params) 
	{
		if (is_array($params)
            && isset($params["vercode"])
            && isset($params["mid"])
            && isset($params["stat"])
        ) {

            $verccode = $params["vercode"];
            $mid = $params["mid"];
            $stat = $params["stat"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE alldevice set vercode='$vercode' stat='$stat' WHERE mid='$mid';
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
	
	public static function _List($params) 
	{
		$db = Db::init();

        $re = array("devices"=>array());
        $results = $db->get_results(<<<EOD
select mid,vercode,stat,type from alldevice
EOD
);
        if (isset($results[0])) {
            foreach ($results as $rec)
            {
                $re["devices"][]=array(
                    "mid"=>$rec->mid*1, 
                    "vercode"=>$rec->vercode,
					"stat"=>$rec->stat,
					"type"=>$rec->type
                );
            }
        }
        return Error::getRetString(0, $re);
	}
	
	public static function _Verify($params) 
	{
		if (is_array($params)
			&& isset($params['mid'])
		) {
			$db = Db::init();
			$mid = $params['mid'];
			
			if($db->query(<<<EOD
select mid from alldevice where mid=$mid
EOD
			)) {
				return Error::getRetString(0);
			}
			return Error::getRetString(10007);		
		}
	}
}
?>
