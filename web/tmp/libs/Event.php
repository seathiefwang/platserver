<?php

/**
 * 
 * 主逻辑
 * 主要是处理 onMessage 方法
 * @author walkor <walkor@workerman.net>
 * 
 */

class Event
{
    /**
     * 有消息时
     * @param string $message
     */
    public static function procMessage($data, $class_dir)
    {
        // 判断数据是否正确
        if(empty($data['class']) || empty($data['method']) )
        {
            return json_encode((array('code'=>1, 'msg'=>'bad request', 'data'=>null)));
        }
        // 获得要调用的类、方法、及参数
        $class = $data['class'];
        $method = $data['method'];

        if(isset($data['params']))
        {
            $param_array = $data['params'];
        }
        else
        {
            $param_array = array();
        }

        $class="_".$class;
        $method = "_".$method;
        $success = false;
        // 判断类对应文件是否载入
        if(!class_exists($class))
        {
            $include_file = $class_dir."/$class.php";
            if(is_file($include_file))
            {
                require_once $include_file;
            }
            if(!class_exists($class))
            {
                $code = 33404;
                $msg = "class $class not found";
                // 发送数据给客户端 类不存在
                //var_dump($connection);
                return json_encode(array('code'=>$code, 'msg'=>$msg));
            }
        }

        // 调用类的方法
        try 
        {
            $ret = call_user_func_array(array($class, $method), array($param_array));
            // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
            if(isset($ret))
            { 
                //var_dump($ret);
                return $ret;
            }
            else
            {
                return json_encode(array('code'=>33405, 'msg'=>"Method $class.$method invoke error"));
            }
        }
        // 有异常
        catch(Exception $e)
        {
            // 发送数据给客户端，发生异常，调用失败
            $code = $e->getCode() ? $e->getCode() : 500;
            return json_encode(array('code'=>$code, 'msg'=>$e->getMessage(), 'result'=>$e));
        }
    }

    public static function onMessage($data, $class_dir)
    {
        echo Event::procMessage($data, $class_dir);
    }
}

