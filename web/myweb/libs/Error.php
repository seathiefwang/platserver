<?php


require_once ("Protocol.php");
/**
 * 错误信息
 * @author 律鑫
 * @version 1.0
 * @updated 09-六月-2015 11:20:02
 */
class Error
{

	/**
	 * 保存错误信息
	 */
	private $errorMsg;
	public $m_Protocol;

	public function __construct()
	{
	}

	public function __destruct()
	{
	}

	/**
	 * 获得返回字符串
	 * 
	 * @param code    全局返回码
	 * @param results    results值
	 */
	public static function getRetString($code, $results = null)
	{
		$msg = "未知错误，错误代码($code)";
        switch($code)
        {
        case 0:
            $msg = "ok";
            break;
        case 1:
            $msg = "错误";
            break;
        }
        if ($results!=null)
            return Protocol::encode(array("code"=>$code,"msg"=>$msg, "results"=>$results));
        else
            return Protocol::encode(array("code"=>$code,"msg"=>$msg));
	}

}
?>
