<?php


require_once (__DIR__."/../core/Error.php");
/**
 * 主机管理类
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
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
     * 
     * @param params    参数列表
     */
    public static function _Bind($params)
    {
        if (is_array($params)
            && isset($params["user"])
            && isset($params["udid"])
            && isset($params["name"])
        )
        {
            $user = $params["user"]; 
            $name = $params["name"]; 
            $uuid=$_GET["uuid"];
            $udid = $params["udid"]; 

            $db = Db::init();
            //主机是否已经被绑定
            if ($db->query(<<<EOD
select udid from user_device where udid="$udid"
EOD
        ))
            {
                return Error::getRetString(10030);
            }

            //执行主机绑定
            if($db->query(<<<EOD
INSERT INTO user_device(user, udid, name) SELECT '$user', '$udid','$name' FROM DUAL WHERE EXISTS(SELECT user FROM user WHERE uuid="$uuid" and user="$user" and priv=3);
EOD
        ))
            {
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
     * @param params    参数列表
     */
    public static function _Unbind($params)
    {
        if (is_array($params)
            && isset($params["user"])
            && isset($params["udid"])
        )
        {
            $user = $params["user"]; 
            $uuid=$_GET["uuid"];
            $udid = $params["udid"]; 

            $db = Db::init();

            //执行主机解绑
            if($db->query(<<<EOD
delete from user_device WHERE EXISTS(SELECT user FROM user WHERE uuid="$uuid" and user="$user" and priv=3) and user="$user" and udid="$udid";
EOD
        ))
            {
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
     * @param params    参数列表
     */
    public static function _Rename($params)
    {
        if (is_array($params)
            && isset($params["user"])
            && isset($params["udid"])
            && isset($params["name"])
        )
        {
            $user = $params["user"]; 
            $name = $params["name"]; 
            $uuid=$_GET["uuid"];
            $udid = $params["udid"]; 

            $db = Db::init();

            //执行主机改名
            if($db->query(<<<EOD
update user_device set name="$name" WHERE EXISTS(SELECT user FROM user WHERE uuid="$uuid" and user="$user" and priv=3) and user="$user" and udid="$udid";
EOD
        ))
            {
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

}
?>
