<?PHP

require_once (__DIR__."/../libs/Error.php");
include_once (__DIR__."/../db/Db.php");

class _User
{
	
    public $m_Error;
	
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
		if (is_array($params)
			&& isset($params['name'])
			&& isset($params['email'])
			&& isset($params['sex'])
			&& isset($params['phone'])
			&& isset($params['qq'])
			&& isset($params['address'])) {
			$name   = $params["name"];
            $email  = $params["email"];
            $sex    = $params["sex"];
            $phone  = $params["phone"];
            $qq     = $params["qq"];
            $address= $params["address"];
			
			$db = Db::init();
            //是否已存在
            if ($db->query(<<<EOD
select email from user where email="$email"
EOD
			) || $db->query(<<<EOD
select phone from user where phone="$phone"
EOD
			)) {
				return Error::getRetString(10020);
			}
						
            $db->query(<<<EOD
INSERT INTO user(name, sex, phone, email, QQ, address) SELECT '$name', '$sex', '$phone', '$email', '$qq', '$address' FROM DUAL 
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
				$var = $db->get_var("select uuid from user where email=\"$email\"");
						if($var === $uuid) {
							return Error::getRetString(0);
						}
			}						
		}
		return Error::getRetString(10007);
	}
	
	/**
     * 
     * @param params    params
     */
    public static function _GetInfo($params)
    {
        if (isset($params["email"])
        )
        {
            $db = Db::init();
            $email = $params["email"];
            
            //是否已存在
            if (!$db->query(<<<EOD
select email from user where email="$email"
EOD
        ))
            {
                return Error::getRetString(10020);
            }
            
            $info = $db->get_row("select * from user where email='$email'");

            return Error::getRetString(0, array(
                "name"=>$info->name,
                "email"=>$info->email,
                "sex"=>$info->sex,
                "phone"=>$info->phone,
                "QQ"=>$info->QQ,
                "address"=>$info->address,
                ));
        }
        else
        {
            return Error::getRetString(10007);
        }
    }
}
?>