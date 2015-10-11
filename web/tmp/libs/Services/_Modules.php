<?php
require_once __DIR__."/../db/db.php";
require_once __DIR__."/../Uart.php";
require_once __DIR__."/../functions.php";
require_once __DIR__."/_Scene.php";

//error_reporting(0);
class _Modules
{
    public static function _Add($params)
    {
        if (isset($params["devclass"])
            && isset($params["mid"])
            && isset($params["name"][0])
        )
        {
            $devclass = $params["devclass"]; 
            $mid = strtoupper($params["mid"]);  
            $devname = $params["name"];
            $db=Db::init();

            if ($devclass == "")
                return json_encode(array('code'=>32007, 'msg'=>"devclass error"));
            if (strlen($mid)!=8)
                return json_encode(array('code'=>32008, 'msg'=>"mid error"));
            if (!is_array($devname))
                return json_encode(array('code'=>32009, 'msg'=>"devinfo error"));

            if($db->get_results('SELECT * FROM "Modules" WHERE mid="'.$mid.'";'))
            { //ID已经存在
                //return json_encode(array('code'=>32002, 'msg'=>"mid already exist"));
                $db->query("delete from mod_child where mid='$mid';");
                $db->query("delete from Modules where mid='$mid';");
                //$db->query("update from switch_info where switchid='$mid';");
                $db->query('UPDATE "switch_info" SET "switchid"=0 WHERE ("switchid"=\''.$mid.'\')');
                
            }

            //判断子设备名称是否已存在
            foreach($devname as $rec=>$value)
            {
                if (isset($value["name"]) && is_string($value["name"]))
                {
                    $name=$value["name"];
                    if ($db->query("select name from mod_child where name='$name'"))
                    {
                        return urldecode(
                            json_encode(
                                array(
                                    'code'=>32010, 
                                    'msg'=>"child device name already exist", 
                                    'data'=>urlencode($name)
                                )
                            )
                        );
                    }
                }
                else
                {
                    return json_encode(array('code'=>32009, 'msg'=>"devinfo error"));
                }
            }
            //$child_total = count($params["name"])-1;


            $inst_type = 0;
            //通过串口发送绑定指令
                if ($devclass=="Switch") //绑定开关不需要等待回复
                {
                    //++++获得空闲的number值
                    $idle_id = $db->get_var('SELECT max(number) FROM "switch_info" where switchid="0"');
                    if(isset($idle_id))
                    { //找到可用ID
                        $inst_type = 1;
                    }
                    else
                    {
                        $idle_id = $db->get_var('SELECT max(number)+1 FROM "switch_info"');
                        if (!isset($idle_id))
                        {
                            $idle_id = 1;
                        }
                        if ($idle_id > 0xff)
                        {
                            return "超出范围\n";
                        }
                        $inst_type = 2;
                    }

                    $dat = sprintf ("%02X", $idle_id);
                    $json=array("id"=>$mid,"code"=>0, "param"=>0, "data"=>"$dat","timeout"=>500,"response"=>0);
                    $res = Uart::send (json_encode($json));

                    //----获得空闲的number值
                }
                else//需要等待模块回复绑定结果
                {
                    $json=array("id"=>$mid,"code"=>0, "param"=>0, "data"=>"","timeout"=>500,"response"=>1);
                    $res = Uart::send (json_encode($json));
                }

            //INSERT INTO t1 VALUES((SELECT max(a) FROM t1)+1,123);
            if ($res["stat"] == 0)
            {
                if ($devclass=="Switch")
                {
                    $db->query(<<<EOD
INSERT INTO "Modules" ("type", "mid", "extra") VALUES ('$devclass', '$mid', (SELECT max(extra) FROM Modules)+1);
EOD
                );

                    switch ($inst_type)
                    {
                    case 1:
                        $db->query('UPDATE "switch_info" SET "switchid"="'.$mid.'" WHERE ("number"='.$idle_id.')');
                        break;
                    case 2:
                        $db->query('INSERT INTO "switch_info" ("switchid", "number") VALUES ("'.$mid.'", '.$idle_id.')');
                        break;
                    default:
                        break;
                    }
                }
                else
                {
                    $db->query(<<<EOD
INSERT INTO "Modules" ("type", "mid") VALUES ('$devclass', '$mid');
EOD
            );
                }

                foreach ( $devname as $child )
                {
                    $name = $child["name"];
                    $no = $child["no"];

                    $db->query("INSERT INTO mod_child (`mid`, `no`, `name`) VALUES ('$mid', '$no', '$name');");
                }

                return json_encode(array('code'=>0, 'msg'=>"ok"));
            }
            else
            {
                return json_encode(array('code'=>32004, 'msg'=>"communication fault(".$res["stat"].")"));
            }
        }
        return null;
    }
    public static function _Del($params)
    {
        if (isset($params["mid"]))
        {
            $mid = strtoupper($params["mid"]);
            $db = Db::init();
            $db->query("delete from mod_child where mid='$mid';");
            $db->query("delete from Modules where mid='$mid';");
            $db->query("delete from switch_info where switchid='$mid';");
            _Scene::EraseMid($mid);
            return json_encode(array('code'=>0, 'msg'=>"ok"));
        }
        return null;
    }
    public static function _ListAll($params)
    {
        {
            $db = Db::init();
            $device_arr = array();
            $devlist = $db->get_results("select * from Modules;");

            if ($devlist[0]) //确保是数组
            {
                foreach($devlist as $dev)
                {
                    $child_arr = array();
                    $childlist = $db->get_results("select no, name from mod_child where mid='$dev->mid';");
                    if ($childlist[0])
                    {
                        foreach($childlist as $child)
                        {
                            $child_arr[] = array("no"=>$child->no, "name"=>urlencode($child->name));
                        }
                    }
                    $device_arr[] = array("devclass"=>urlencode($dev->type), "mid"=>$dev->mid, "name"=>$child_arr);
                }
            }
            $results = array("devices"=>$device_arr);
            return urldecode(json_encode(array("code"=>0, "msg"=>"ok", "results"=>$results))); 
        }
        return null;
    }

    //检查设备是否合法
    public static function _Check($params)
    {
        if (isset($params["devclass"])
            && isset($params["mid"])
        )
        {
            $devclass = $params["devclass"];
            $mid = $params["mid"];

            $db = Db::init();
            $device_arr = array();
            if($db->query("select mid from Modules where mid='$mid' and type='$devclass'"))
            {
                return json_encode(array('code'=>0, 'msg'=>"ok"));//mid合法 
            }
            else
            {
                return json_encode(array('code'=>30004, 'msg'=>"mid invalid")); //mid不合法
            }
        }
        return null;
    }
    public static function _List($params)
    {
        if (isset($params["devclass"]))
        {
            $devclass = $params["devclass"];
            $db = Db::init();
            $device_arr = array();
            $devlist = $db->get_col("select mid from Modules where type='$devclass';");
            $stat = -1;

            foreach($devlist as $dev)
            {
                if ($devclass=="Socket")
                {
                    $stat = device_query_stat($dev);
                }

                $child_arr = array();
                $childlist = $db->get_results("select no, name from mod_child where mid='$dev';");
                foreach($childlist as $child)
                {
                    $child_arr[] = array("no"=>$child->no, "name"=>urlencode($child->name), "stat"=>$stat);
                }
                $device_arr[] = array("mid"=>$dev, "name"=>$child_arr);
            }
            $results = array("devices"=>$device_arr);
            return urldecode(json_encode(array("code"=>0, "msg"=>"ok", "results"=>$results))); 
        }
        return null;
    }

}
