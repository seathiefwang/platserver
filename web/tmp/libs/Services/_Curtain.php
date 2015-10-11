<?php

require_once (__DIR__."/../Uart.php");
require_once (__DIR__."/../functions.php");

class _Curtain
{
    public static function _Open($params)
    {
        return device_oper($params, 100);
    }

    public static function _Close($params)
    {
        return device_oper($params, 101);
    }
    public static function _TurnTog($params)
    {
        return device_oper($params, 102);
    }
    public static function _AllOpen($params)
    {
        return device_oper($params, 103);
    }
    public static function _AllClose($params)
    {
        return device_oper($params, 104);
    }
    public static function _Query($params)
    {
        return json_encode(array('code'=>33401, 'msg'=>"Reserved interface")); //保留接口
        //return _turn($params, 107, true);
    }
}
?>
