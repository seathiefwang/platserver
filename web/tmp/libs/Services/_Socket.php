<?php

require_once (__DIR__."/../Uart.php");
require_once (__DIR__."/../functions.php");

class _Socket
{
    public static function _TurnOn($params)
    {
        return device_oper($params, 100);
    }

    public static function _TurnOff($params)
    {
        return device_oper($params, 101);
    }
    public static function _TurnTog($params)
    {
        return device_oper($params, 102, false, 0);
    }
    public static function _AllOn($params)
    {
        return device_oper($params, 103);
    }
    public static function _AllOff($params)
    {
        return device_oper($params, 104);
    }
    public static function _Query($params)
    {
        return device_oper($params, 107, true);
    }
}
?>
