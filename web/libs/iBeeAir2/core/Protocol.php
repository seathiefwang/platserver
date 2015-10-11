<?php


/**
 * 输入输出编解码协议
 * @author 律鑫
 * @version 1.0
 * @updated 09-六月-2015 11:20:02
 */
class Protocol
{

	function __construct()
	{
	}

	function __destruct()
	{
	}



	/**
	 * 向客户端回复数据时调用
	 * 
	 * @param buffer
	 */
	public static function encode($buffer)
	{
            return preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", json_encode($buffer))."\n";
	}

	/**
	 * 收到数据后调用
	 * 
	 * @param buffer
	 */
	public static function decode($buffer)
	{
            return json_decode($buffer, true);
	}

}
?>
