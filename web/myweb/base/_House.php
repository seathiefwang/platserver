<?PHP

require_once (__DIR__."/../libs/Error.php");
include_once (__DIR__."/../db/Db.php");

class _House
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
			&& isset($params['mid'])
			&& isset($params['vercode'])
			) {
			$email=$_GET["u"];
			$mid = $params['mid'];
			$vercode = $params['vercode'];
			
			
			$db = Db::init();
            //是否已存在udevice中
            if ($db->query(<<<EOD
select mid from udevice where mid="$mid" and email="$email"
EOD
			)) {
				return Error::getRetString(10020);
			} 
			//是否已存在mdevice中
			if ($db->query(<<<EOD
select mid from mdevice where mid="$mid"
EOD
			)) {
				$results = $db->get_row(<<<EOD
select vercode, name from mdevice where mid="$mid"
EOD
				);
				if($results->vercode !== $vercode) {
					return Error::getRetString(10020);
				}
				
				$name = $results->name;
				
				$db->query(<<<EOD
INSERT INTO udevice(email, mid, vercode, name) SELECT '$email', '$mid', '$vercode', '$name' FROM DUAL
EOD
				);
			//不存在mdevice中
			} else {
				//查找所有设备的数据库主表
				if (!$db->query(<<<EOD
select mid from alldevice where mid="$mid"
EOD
				)) {
					return Error::getRetString(10007);
				}
				
				if(!isset($params['name'])) {
					return Error::getRetString(10007);
				}
				$name = $params['name'];
				$db->query(<<<EOD
INSERT INTO mdevice(mid, vercode, name) SELECT '$mid', '$vercode', '$name' FROM DUAL
EOD
				);
				$db->query(<<<EOD
INSERT INTO udevice(email, mid, vercode, name) SELECT '$email', '$mid', '$vercode', '$name' FROM DUAL
EOD
				);
			}
			return Error::getRetString(0);
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
			&& isset($params['mid'])
			) {
			$email=$_GET["u"];
            $mid = $params["mid"];
			
			$db = Db::init();
			//执行删除操作
            if ($db->query(<<<EOD
delete from udevice where email="$email" and mid="$mid"
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
    public static function _Modify($params)
    {
        if (is_array($params)
            && isset($params["mid"])
            && isset($params["name"])
        ) {
            $email=$_GET["u"];
            $name = $params["name"];
            $mid= $params["mid"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE udevice set name='$name' WHERE mid='$mid';
EOD
			)&&$db->query(<<<EOD
UPDATE mdevice set name='$name' WHERE mid='$mid';
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
    public static function SetMainDevice($params)
    {
        if (is_array($params)
            && isset($params["houseid"])
            && isset($params["mid"])
            && isset($params["vercode"])
        ) {
            $email=$_GET["u"];

            $houseid = $params["houseid"];
            $mid = $params["mid"];
            $vercode = $params["vercode"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE house set `mid`='$mid',`vercode`='$vercode' WHERE email='$email' and houseid='$houseid';
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
    public static function _ModifyVercode($params)
    {
        if (is_array($params)           
            && isset($params["mid"])
            && isset($params["vercodeOfOld"])
			&& isset($params["vercodeOfNew"])
        ) {
            $email=$_GET["u"];

            $mid = $params["mid"];
            $overcode = $params["vercodeOfOld"];
			$nvercode = $params["vercodeOfNew"];
			
            $db = Db::init();

			if($var = $db->get_var(<<<EOD
select vercode from mdevice where mid='$mid';
EOD
        )) {
				if($var == $overcode) {
					if($db->query(<<<EOD
UPDATE udevice set `vercode`='$nvercode' WHERE mid='$mid' and email='$email';
EOD
					)	
					&&$db->query(<<<EOD
UPDATE mdevice set `vercode`='$nvercode' WHERE mid='$mid';
EOD
					)) {//成功
						return Error::getRetString(0);
					} else {//区域不存在
						return Error::getRetString(10021);
					}
				} else if($var == $nvercode) {
					if($db->query(<<<EOD
UPDATE udevice set `vercode`='$nvercode' WHERE mid='$mid' and email='$email';
EOD
					)) {
						return Error::getRetString(0);
					}
				} else {
					return Error::getRetString(10021);
				}
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
    public static function _List($params)
    {
        $db = Db::init();
        $email=$_GET["u"];


        $re = array("houses"=>array());
        $results = $db->get_results("select mid,vercode,name from udevice where email=\"$email\"");
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $re["houses"][]=array(
                    "name"=>(string)$rec->name,
                    "mid"=>(string)$rec->mid,
                    "vercode"=>(string)$rec->vercode,
                );
            }
        }
        return Error::getRetString(0, $re);
    }

}
?>