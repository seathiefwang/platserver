<?php

require_once (__DIR__."/../Uart.php");
require_once __DIR__."/../db/db.php";
require_once (__DIR__."/../functions.php");

function _turn($params,$code, $all=false)
{
    $db = Db::init();
    if ($all == true || (isset($params["mid"])
        && isset($params["no"]))
    )
    {
        $mid = strtoupper($params["mid"]);
        $no =  $params["no"];

       
        $type = substr($mid, 0,1)."\n";

        if (verify_mid($mid)==false)
        {  //id不存在
            return json_encode(array('code'=>30004, 'msg'=>"mid no exist"));
        }

        $data = $db->get_var('SELECT number FROM "switch_info" where switchid="'.$mid.'"');
        if (isset($data))
        {
            $data = sprintf("%02X", $data);
            switch ($type)
            {
            case '1'://电池版开关
                $json=array("id"=>"$mid","code"=>$code, "param"=>(1<<($no-1)), "data"=>"$data","timeout"=>2000, "response"=>0);
                break;
            case '4'://零火版开关
            default:
                $json=array("id"=>"$mid","code"=>$code, "param"=>(1<<($no-1)), "data"=>"$data","timeout"=>500, "response"=>0);
                break;
            }

            for($i=0; $i < 100; $i ++){
                $res = Uart::send (json_encode($json));
                if(isset($res["stat"]))
                {
                    switch ($res["stat"])
                    {
                    case 0:
                        return json_encode(array('code'=>0, 'msg'=>"ok"));
                        break;
                    case 40004://射频模块正忙
                        usleep(200000);
                        continue;
                        return json_encode(array('code'=>30006, 'msg'=>"system is busy"));
                    default:
                        return json_encode(array('code'=>32004, 'msg'=>"communication fault(".$res["stat"].")"));
                    }
                }
            }
            return json_encode(array('code'=>32004, 'msg'=>"communication fault(".$res["stat"].")"));
        }
        else
        {
            //读数据库错误
            return json_encode(array('code'=>30005, 'msg'=>"read data from database error")); 
        }
    }
    else
    {
        return json_encode(array('code'=>30003, 'msg'=>"params error")); //参数不完整
    }
}

class _Switch
{
    public static function _TurnOn($params)
    {
        return _turn($params, 100);
    }

    public static function _TurnOff($params)
    {
        return _turn($params, 101);
    }
    public static function _TurnTog($params)
    {
        return _turn($params, 102);
    }
    public static function _AllOn($params)
    {
        return _turn($params, 103, true);
    }
    public static function _AllOff($params)
    {
        return _turn($params, 104, true);
    }
    public static function _Query($params)
    {
        return json_encode(array('code'=>33401, 'msg'=>"Reserved interface")); //保留接口
    }
}
?>
