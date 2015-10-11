<?php


require_once (__DIR__."/../core/Error.php");
include_once __DIR__.("/../../Db.php");
/**
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
class _User
{

    public $m_Error;

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

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
    /**
     * 
     * @param params    params
     */
    public static function _Register($params)
    {
        if (is_array($params)
            && isset($params["name"])
            && isset($params["email"])
            && isset($params["sex"])
            && isset($params["tel"])
            && isset($params["qq"])
            && isset($params["address"])
        )
        {
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
        ))
            {
                return Error::getRetString(10020);
            }

            $db->query(<<<EOD
INSERT INTO user(name, sex, tel, email, QQ, address) SELECT '$name', '$sex', '$tel', '$email', '$qq', '$address' FROM DUAL 
EOD
        );
            return Error::getRetString(0);
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
    public static function _Login($params)
    {
        if (is_array($params)
            && isset($params["account"])
        )
        {
            $account = $params["account"];
            $db = Db::init();
            $uuid = self::createUUID();
            if ($db->query("select email from user where email=\"$account\""))
            {
                $db->query("update user set uuid=\"$uuid\" where email=\"$account\"");
                return Error::getRetString(0, array("uuid"=>$uuid));
            }
        }
        else
        {
            return Error::getRetString(10007);
        }
    }


}
?>
