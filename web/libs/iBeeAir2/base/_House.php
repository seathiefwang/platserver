<?php


require_once (__DIR__."/../core/Error.php");
include_once __DIR__.("/../../Db.php");
/**
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
class _House
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
            && isset($params["name"])
        )
        {
            $name = $params["name"]; 
            $email=$_GET["u"];

            $db = Db::init();
            //是否已存在
            if ($db->query(<<<EOD
select houseid from house where name="$name" and email="$email"
EOD
        ))
            {
                return Error::getRetString(10020);
            }

            $db->query(<<<EOD
INSERT INTO house(email, name) SELECT '$email', '$name' FROM DUAL
EOD
        );
            return Error::getRetString(0, array("houseid"=>$db->get_var("select houseid from house where email=\"$email\" and name=\"$name\"")));
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
            && isset($params["houseid"])
        )
        {
            $email=$_GET["u"];
            $houseid = $params["houseid"];

            $db = Db::init();

            //执行删除操作
            if ($db->query(<<<EOD
delete from house where email="$email" and houseid="$houseid"
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
            && isset($params["houseid"])
            && isset($params["name"])
        )
        {
            $email=$_GET["u"];
            $name = $params["name"];
            $houseid= $params["houseid"];

            $db = Db::init();

            if($db->query(<<<EOD
UPDATE house set name='$name' WHERE email='$email' and houseid='$houseid';
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
    public static function _SetMainDevice($params)
    {
        if (is_array($params)
            && isset($params["houseid"])
            && isset($params["mid"])
            && isset($params["vercode"])
        )
        {
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
    public static function _List($params)
    {
        $db = Db::init();
        $email=$_GET["u"];


        $re = array("houses"=>array());
        $results = $db->get_results("select houseid,name,mid,vercode from house where email=\"$email\"");
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $re["houses"][]=array(
                    "houseid"=>intval($rec->houseid), 
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