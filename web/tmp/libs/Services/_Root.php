<?php
require_once __DIR__."/../db/db.php";

//error_reporting(0);
class _Root
{
    //主机解除绑定
    public static function _Unbind($params)
    {
        if (isset($params["udid"])
            &&isset($params["user"])
        )
        {
            $udid = $params["udid"];
            $user = $params["user"];

            exec("/usr/bin/getudid.sh", $devid);
            if (strcmp($devid[0],$udid) != 0)
                return json_encode(array('code'=>32005, 'msg'=>"udid error"));
            $db = Db::init();
            if($db->query("delete from authorization where user='$user'"))
            {
                return json_encode(array('code'=>0, 'msg'=>"ok"));
            }
            else
            {
                return json_encode(array('code'=>32001, 'msg'=>"user is not exist"));
            }
        }
        return null;
    }

    //获得绑定用户列表
    public static function _List($params)
    {
            $db = Db::init();
            $tdidlist = $db->get_col("select user from authorization where stat=1");

            $results = array();
            foreach($tdidlist as $tdid)
            {
                $results[]=array("user"=>$tdid);
            }
            return json_encode(array("code"=>0, "msg"=>"ok", "results"=>$results)); 
    }
}
