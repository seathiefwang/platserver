<?php


require_once (__DIR__."/../libs/DeviceControl.php");
/**
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
class _Curtain extends DeviceControl
{

	public function __construct() {}

	public function __destruct() {}

	/**
	 * 开窗帘
	 * 
	 * @param params    params
	 */
	public static function _Open($params)
	{
		return device_oper($params, 100);
	}

	/**
	 * 关窗帘
	 * 
	 * @param params    params
	 */
	public static function _Close($params)
	{
		return device_oper($params, 101);
	}

	/**
	 * 电机停止
	 * 
	 * @param params    params
	 */
	public static function _Stop($params)
	{
		return device_oper($params, 000);
	}
	
	/**
	 * 电机停止
	 * 
	 * @param params    params
	 */
	public static function _TurnTog($params)
    {
        return device_oper($params, 102);
    }
	
	/**
	 * 电机停止
	 * 
	 * @param params    params
	 */
    public static function _AllOpen($params)
    {
        return device_oper($params, 103);
    }
	
	/**
	 * 电机停止
	 * 
	 * @param params    params
	 */
    public static function _AllClose($params)
    {
        return device_oper($params, 104);
    }
	
	/**
	 * 查询状态
	 * 
	 * @param params    params
	 */
	public static function _Query($params)
	{
		return json_encode(array('code'=>33401, 'msg'=>"Reserved interface")); //保留接口
	}

}
?>