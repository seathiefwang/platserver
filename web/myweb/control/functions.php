<?php
require_once (__DIR__."../db/db.php");

function verify_mid($mid)
{
    $db = Db::init();
    $result = $db->get_results("select * from Modules where mid='$mid'");
    //var_dump($result);
    if (isset($result))
        return true;
    return false;
}

function verify_tdid($tdid)
{
    $db = Db::init();
    $result = $db->get_results("select * from root where event='tdid' and value='$tdid'");
    //var_dump($result);
    if (isset($result))
        return true;
    return false;
}

function verify_user($user)
{
    $db = Db::init();
    $result = $db->get_results("select user from authorization where stat=1 and user='$user'");
    //var_dump($result);
    if (isset($result[0]))
        return true;
    return false;
}

//控制插座、窗帘
function device_oper($params,$code, $query=false, $response=1)
{
    if (isset($params["did"]))
    {
        $did = strtoupper($params["did"]);

        if (verify_mid($did)==false)
        {  //id不存在
            return json_encode(array('code'=>30004, 'msg'=>"mid no exist"));
        }

        $json=array(
            "id"=>"$did",
            "code"=>$code, 
            "param"=>1, 
            "data"=>"",
            "timeout"=>600,
            "response"=>$response);
        for($i=0; $i < 1000; $i++)
        {
            $res = Uart::send (json_encode($json));
            if(isset($res["stat"]))
            {
                switch ($res["stat"])
                {
                case 0:
                    if ($query)
                    {
                        //var_dump($res);
                        return json_encode(array('code'=>0, 'msg'=>"ok", 'results'=>array('stat'=>$res["result"]["param"])));
                    }
                    else
                    {
                        return json_encode(array('code'=>0, 'msg'=>"ok"));
                    }
                    break;
                case 40004://射频模块正忙
                    usleep(1000);
                    continue;
                    //return json_encode(array('code'=>30006, 'msg'=>"system is busy"));
                default:
                    return json_encode(array('code'=>32004, 'msg'=>"communication fault(".$res["stat"].")"));
                }
            }
            else
            {
                return json_encode(array('code'=>30006, 'msg'=>"system is busy"));
            }
        }
        return json_encode($res);  
    }
    else
    {
        return json_encode(array('code'=>30003, 'msg'=>"params error")); //参数不完整
    }
}

//查询设备当前状态
function device_query_stat($mid)
{
    $json=array(
        "id"=>"$mid",
        "code"=>107, 
        "param"=>1, 
        "data"=>"",
        "timeout"=>100,
        "response"=>1);

    do{
        //for ($i = 0; $i < 3; $i ++){
            $res = Uart::send (json_encode($json));
            if (isset($res["stat"]) && $res["stat"] == 0)
            {
                //var_dump($res);     
                return $res["result"]["param"];
            }
            usleep(1000);
        //}
    }
    while (isset($res["stat"]) && $res["stat"] == 40004);

    return -1;
    //var_dump($res);     
}

?>
