<?php
require_once __DIR__."/../db/db.php";

//error_reporting(0);
class _Root
{
    //主机绑定
    public static function _Bind($params)
    {
        if (isset($params["tdid"])
            && isset($params["user"])
            && isset($params["udid"])
        )
        {
            $tdid = $params["tdid"]; 
            $user = $params["user"]; 
            $udid = strtoupper($params["udid"]); 

            $db = Db::init();
            exec("/usr/bin/getudid.sh", $devid);
            //return json_encode(array('code'=>0, 'msg'=>"ok", 'params'=>array('udid'=>"$devid[0]")))."\n";
            if (strcmp($devid[0],$udid) != 0)
                return json_encode(array('code'=>32005, 'msg'=>"udid error"));

            if(!$db->get_results('SELECT * FROM "authorization" WHERE user="'.$user.'";'))
            {
                if (!$db->query('insert into "authorization" ("user","stat") values (\''.$user.'\', 0)'))
                {
                    return json_encode(array('code'=>-1, 'msg'=>"busy"));
                }
            }
            else
            {//如果user存在，则修改状态为绑定中stat=0
                if (!$db->query("update authorization set stat=0 where user='$user'"))
                {
                    return json_encode(array('code'=>-1, 'msg'=>"busy"));
                }

            }


            if($db->get_results('SELECT * FROM "Root" WHERE event="tdid" and value="'.$tdid.'";'))
            { //ID已经存在
                return json_encode(array('code'=>0, 'msg'=>"ok", 'results'=>array("udid"=>$devid[0])));
            }

            $db->query(<<<EOD
INSERT INTO "Root" ("event","value") VALUES ('tdid','$tdid');
EOD
        );
            
            return json_encode(array('code'=>0, 'msg'=>"ok", 'results'=>array("udid"=>$devid[0])));
        }
        return json_encode(array('code'=>30003, 'msg'=>"error"));
    }

    //主机绑定
    public static function _UserBind($params)
    {
        if (isset($params["user"])
        )
        {
            $user = $params["user"]; 

            $db = Db::init();

            if(!$db->get_results('SELECT * FROM "authorization" WHERE user="'.$user.'" and stat=0;'))
            {
                return json_encode(array('code'=>32006, 'msg'=>"permission denied"));
            }

            $db->query(<<<EOD
update authorization set stat=1 where user="$user" and stat=0;
EOD
        );

            return json_encode(array('code'=>0, 'msg'=>"ok"));
        }
        return json_encode(array('code'=>30003, 'msg'=>"error"));
    }

    //获得绑定手机列表
    public static function _List($params)
    {
            $db = Db::init();
            $tdidlist = $db->get_col("select value from root where event='tdid'");

            foreach($tdidlist as $tdid)
            {
                echo "$tdid\n";
            }
            return json_encode(array("code"=>0, "msg"=>"ok", "results"=>$results)); 
    }

    public static function _GetDatebase($params)
    {
        $db=Db::init();

        $res = $db->get_results("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
        //var_dump($res);

        $arr_tables=array();

        //遍历表
        foreach ($res as $table)
        {
            $recodes = $db->get_results("SELECT * FROM $table->name;");
            $arr_tables["$table->name"]=array();
            //遍历表中所有元素
            foreach ($recodes as $rec)
            {
                $arr_record=array();
                //保存所有元素
                foreach ($rec as $class=>$value)
                {
                    $arr_record["$class"]=$value;
                }
                $arr_tables["$table->name"][]=$arr_record;
            }
        }
        return json_encode ($arr_tables);
    }
}
