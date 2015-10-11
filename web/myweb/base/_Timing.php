<?php


require_once (__DIR__."/../libs/Error.php");
include_once (__DIR__."/../db/Db.php");
require_once (__DIR__."/../libs/Action.php");
/**
 * 托管类
 * @author 律鑫
 * @version 1.0
 * @created 09-六月-2015 11:20:02
 */
 
class _Timing
{

	public $m_Error;

	public function __construct()
	{
	}

	public function __destruct()
	{
	}

	/**
	 * 
	 * @param params    params
	 */
	public static function _Create($params)
	{
		if (isset($params['time'])
			&&isset($params['email'])) {
				
			$hour = substr($params['time'],0,2);
			$minute = substr($params['time'],3,2);
			//30 5 * * * ls 
			$data = $minute.' '.$hour.' * * * php -f /var/www/html/extral/manage/assistant.php '.$params['time']."\n";
			
			$file = file("/var/spool/cron/apache");
			$tag = false;
			foreach($file as $key=>$value)
			{
				if(strpos($value, $data) !== FALSE)
				{
//					unset($file[$key]);
					$tag = true;
				}
			}
			if(!$tag) $file[] = $data;
			file_put_contents('/var/spool/cron/apache', implode("", $file),LOCK_EX);

		}
	}

	/**
	 * 
	 * @param params    params
	 */
	public static function _Destroy($params)
	{
	}

	/**
	 * 获得托管模式列表
	 * 
	 * @param params    params
	 */
	public static function _List($params)
	{
	}

	/**
	 * 获得模式信息
	 * 
	 * @param params    params
	 */
	public static function _GetInfo($params)
	{
	}

	/**
	 * 修改模式信息
	 * 
	 * @param params    params
	 */
	public static function _Modify($params)
	{
	}

	/**
	 * 使能托管模式
	 * 
	 * @param params    params
	 */
	public static function _Enable($params)
	{
		file_put_contents("./test.txt", "run\n", FILE_APPEND);
		$db = Db::init();
		$time = $params['time'];
		$results = $db->get_results("select assid from assistant where time='$time'");
		if(isset($results[0]))
			foreach($results as $re) {
				$invokes = $db->get_results("select * from assi_info where assid=$re->assid");
				if(isset($invokes[0]))
				{
					foreach($invokes as $invoke)
					{
						$row = $db->get_row("select assisEn, notify from device where did='$invoke->did'");
						//var_dump($invoke);
						if($row->assisEn == 1) {
							file_put_contents("./test.txt", "actionrun\n", FILE_APPEND);
							Action::run($invoke->cmdline, $invoke->mid, '慧管家');       
						}							
					}
				}
			}
	}

}
?>