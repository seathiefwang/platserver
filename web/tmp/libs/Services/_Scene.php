<?php
require_once __DIR__."/../db/db.php";
require_once __DIR__."/../Uart.php";
require_once __DIR__."/../Event.php";

//error_reporting(0);
class _Scene
{
    public static function _Create($params)
    {
        //var_dump($params)
        if (isset($params["name"])
            && is_array($params["invokes"])
        )
        {
            $db = Db::init();
            $name = $params["name"];
            if($db->get_results('SELECT * FROM "Scene" WHERE name="'.$name.'";'))
            { //情景模式已经存在
                //return json_encode(array('code'=>31002, 'msg'=>"scene already exist"));
                $scene_id = $db->get_var('select sceneid from "Scene" where name="'.$name.'"');
                $db->query("delete from Scene where name='$name';");
                $db->query("delete from scene_info where id=$scene_id;");
            }

            $db->query('INSERT INTO "Scene" ("name") VALUES ("'.$name.'")');
            $scene_id = $db->get_var('select sceneid from "Scene" where name="'.$name.'"');

            //保存情景模式命令行
            $invokes = $params["invokes"];
            foreach ($invokes as $no=>$cmdline)
            {//处理命令序列
                if (isset($cmdline["class"])
                    &&isset($cmdline["method"])
                    &&isset($cmdline["params"])
                    &&isset($cmdline["params"]["mid"])
                )
                {
                    //$text=>preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", json_encode($cmdline)),
                    $db->query('INSERT INTO "scene_info" ("id","order","cmdline","mid") VALUES ('.$scene_id.','.$no.',\''.json_encode($cmdline).'\',\''.strtoupper($cmdline["params"]["mid"]).'\')');
                }
                else
                {
                    return json_encode(array('code'=>31004, 'msg'=>"invoke json format error"));
                }
            }
            return json_encode(array('code'=>0, 'msg'=>"ok", 'results'=>array("sceneid"=>$scene_id)));
        }
        else
        {
            return json_encode(array('code'=>31003, 'msg'=>"params format error"));
        }
        return null;
    }
    
    public static function _Destroy($params)
    {
        if (isset($params["sceneid"]))
        {
            $db = Db::init();
            $scene_id = $params["sceneid"];
            $db->query("delete from Scene where sceneid=$scene_id;");
            $db->query("delete from scene_info where id=$scene_id;");
            return json_encode(array('code'=>0, 'msg'=>"ok"));
        }
        else
        {
            return json_encode(array('code'=>31003, 'msg'=>"params format error"));
        }
        return null;
    }
    public static function _List($params)
    {
        $db = Db::init();
        $results = $db->get_results('select * from Scene order by sceneid desc');
        $scenes = array();
        if (isset($results[0]))
        {
            foreach ($results as $rec)
            {
                $scenes[]=array("sceneid"=>$rec->sceneid, "name"=>urlencode($rec->name));
            }
        }
        return urldecode(json_encode(array("code"=>0, "msg"=>"ok", "results"=>array("scenes"=>$scenes))));
    }

    public static function _GetInfo($params)
    {
        if (isset($params["sceneid"]))
        {
            $sceneid = $params["sceneid"];

            $db = Db::init();
            $results = $db->get_results('select * from scene_info where id='.$sceneid.' order by "order"');
            $scenes = array();
            if (isset($results[0]))
            {
                foreach ($results as $rec)
                {
                    $scenes[]=json_decode($rec->cmdline);
                }
            }
            return json_encode(array("code"=>0, "msg"=>"ok", "results"=>array("invokes"=>$scenes)));
        }
        else
        {
            return json_encode(array('code'=>31003, 'msg'=>"params format error"));
        }
    }

    public static function _Modify($params)
    {
        if (isset($params["sceneid"])
            && isset($params["name"])
            && isset($params["invokes"][0])
        )
        {
            $db = Db::init();
            $scene_id = $params["sceneid"];
            //$db->query("delete from Scene where sceneid=$scene_id;");

            $results = $db->get_results('select * from Scene where sceneid='.$scene_id);
            if (isset($results[0])) //sceneid存在
            {
                $db->query('UPDATE "Scene" SET "name"=\''.$params["name"].'\' WHERE ("sceneid"='.$scene_id.')');
                    $db->query("delete from scene_info where id=$scene_id;");

                //保存情景模式命令行
                $invokes = $params["invokes"];
                foreach ($invokes as $no=>$cmdline)
                {//处理命令序列
                    if (isset($cmdline["class"])
                        &&isset($cmdline["method"])
                        &&isset($cmdline["params"])
                        &&isset($cmdline["params"]["mid"])
                    )
                    {
                        //$text=>preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", json_encode($cmdline)),
                        $db->query('INSERT INTO "scene_info" ("id","order","cmdline","mid") VALUES ('.$scene_id.','.$no.',\''.json_encode($cmdline).'\',\''.strtoupper($cmdline["params"]["mid"]).'\')');
                    }
                    else
                    {
                        return json_encode(array('code'=>31004, 'msg'=>"invoke json format error"));
                    }
                }
                return json_encode(array("code"=>0, "msg"=>"ok"));
            }
            else
            {
                return json_encode(array("code"=>31001, "msg"=>"sceneid is not exist"));
            }
        }
        else
        {
            return json_encode(array('code'=>31003, 'msg'=>"params format error"));
        }
    }

    public static function _Execute($params)
    {
        if (isset($params["sceneid"]))
        {
            $sceneid = $params["sceneid"];

            $db = Db::init();
            $results = $db->get_results('select * from scene_info where id='.$sceneid.' order by "order"');
            $scenes = array();
            if (isset($results[0]))
            {
                //ob_start();//开始当前代码缓冲
                ////下面输出http的一些头信息
                //header("Connection: close");//告诉浏览器，连接关闭了，这样浏览器就不用等待服务器的响应
                //header("HTTP/1.1 200 OK"); //可以发送200状态码，以这些请求是成功的，要不然可能浏览器会重试，特别是有代理的情况下
                //ob_end_flush();#输出当前缓冲
                //flush();//输出PHP缓冲

                //ignore_user_abort(true); // 后台运行，这个只是运行浏览器关闭，并不是直接就中止返回200状态。
                //set_time_limit(0); // 取消脚本运行时间的超时上限

                foreach ($results as $rec)
                {
                    for($i=0; $i=100; $i++) //busy的情况下，重发
                    {
                        $res = Event::procMessage(json_decode ($rec->cmdline, true), __DIR__); //执行cmdline
                        $json = json_decode($res, true);
                        if ($json["code"] == 30006) continue;
                        else break;
                    }
                }
            }
            return json_encode(array("code"=>0, "msg"=>"ok"));
            //return json_encode(array("code"=>31001, "msg"=>"id not exist"));
        }
        else
        {
            return json_encode(array('code'=>31003, 'msg'=>"params format error"));
        }
    }

    public static function EraseMid($mid)
    {
        $db = Db::init();
        if ($db->query("delete from scene_info where mid='$mid';"))
        {
            return true;
        }
        return false;
    }
}
