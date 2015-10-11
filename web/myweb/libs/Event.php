<?php

require_once ("Error.php");

class Event
{
	public function __construct()
	{
	}

	public function __destruct()
	{
	}
	
	public static function onMessage($data, $class_dir) 
	{
		if(empty($data['class'])||empty($data['method'])) {
			return json_encode((array('code'=>1, 'msg'=>'bad_request')));			
		}
		
		$class = $data['class'];
		$method = $data['method'];
		
		if(isset($data['params'])) {
			$params_array = $data['params'];			
		} else {
			$params_array = array();
		}
		
		$class = '_'.$class;
		$method = '_'.$method;
		$success = false;
		
		if(!class_exists($class)) {
			$include_file = $class_dir."/$class.php";
			if(is_file($include_file)) {
				require_once($include_file);
			} else {
				$code = 33404;
				$msg = "class $class not found";
				return Error::getRetString($code);
			}			
		}
		try {
			$ret = call_user_func_array(array($class, $method), array($params_array));
			if(isset($ret)) {
				return $ret;
			} else {
				return Error::getRetString(33405);
			}
		} catch(Exception $e) {
			$code = $e->getCode() ? $e->getCode() : 500;
			return json_encode(array('code'=>$code, 'msg'=>$e->getMessage(), 'result'=>$e));
		}
	}
}
?>