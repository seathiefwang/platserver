<?PHP

require_once (_DIR_."/../libs/Error.php");
include_once (_DIR_."/../libs/Db.php");

class _User
{
	public function __construct() {}

    public function __destruct() {}
	
	/**
     * 生成UUID 
     * @param params    params
     */
    public static function createUUID(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }
	
	public static function _Register($params)
	{
		if(is_array($params)
			&& isset($params['name'])
			&& isset($params['email'])
			&& isset($params['sex'])
			&& isset($params['tel'])
			&& isset($params['qq'])
			&& isset($params['address'])) {
			 $name   = $params["name"];
            $email  = $params["email"];
            $sex    = $params["sex"];
            $tel    = $params["tel"];
            $qq     = $params["qq"];
            $address= $params["address"];
			
			$db = Db::init();
            //是否已存在
            if ($db->query(<<<EOD
select email from user where email="$email"
EOD
			) || $db->query(<<<EOD
select tel from user where tel="$tel"
EOD
			)) {
				return Error::getRetString(10020);
			}
						
            $db->query(<<<EOD
INSERT INTO user(name, sex, tel, email, QQ, address) SELECT '$name', '$sex', '$tel', '$email', '$qq', '$address' FROM DUAL 
EOD
        );
            return Error::getRetString(0);		
		} else {
			return Error::getRetString(10007);
		}
	}
	
	public static function _Login($params)
	{
		if(is_array($params)
			&& isset($params['account'])) {
			$account = $params["account"];
            $db = Db::init();
            $uuid = self::createUUID();	
			
			 if ($db->query("select email from user where email=\"$account\""))
            {
                $db->query("update user set uuid=\"$uuid\" where email=\"$account\"");
                return Error::getRetString(0, array("uuid"=>$uuid));
            }
		} else {
			return Error::getRetString(10007);
		}
	}
	
	public static function _VerifyUUID($params) 
	{
		if(is_array($params)
			&& isset($params['uuid'])
			&& isset($params['email'])) {
			$uuid = $params['uuid'];
			$email = $params['email'];
			$db = Db::init();
			if ($db->query("select email from user where email=\"$email\"")) {
				$results = $db->get_result("select uuid from user where email=\"$email\"");
				if(isset($results[0])) {
					foreach($results as $re) {
						if($re['uuid'] === $uuid) {
							$results = $db->get_result("select mid,vercode,name from udevice where email=\"$email\"");
							return Error::getRetString(0, results);
						}
					}
				}
			} else {
				return Error::getRetString(10007);
			}
		}
	}
}
?>