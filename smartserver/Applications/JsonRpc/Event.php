<?php
/**
 * 
 * 主逻辑
 * 主要是处理 onMessage onClose 三个方法
 * @author walkor <walkor@workerman.net>
 * 
 */

use \GatewayWorker\Lib\Db;
use \GatewayWorker\Lib\Gateway;
require_once __DIR__ . '/Protocols/JsonNL.php';
require_once __DIR__ . '/Clients/StatisticClient.php';

class Event
{
   /**
    * 连接断开时
    * @param string $message
    */
    public static function onClose($client_id)
    {
        $db = Db::instance('user'); //测试数据库操作
        $db->query ("DELETE FROM `device_login` WHERE `client_id` = $client_id");
        echo $client_id."断开连接\n";
    }

   /**
    * 连接建立时
    * @param string $message
    */
    public static function onConnect($client_id)
    {
        //$_SESSION['UID']="ADSGJAS"; 
        //Gateway::sendToCurrentClient($client_id);
        //Gateway::sendToClient($client_id, "hello");
        echo $client_id."建立连接\n";

    }
    
   /**
    * 有消息时
    * @param int $client_id
    * @param string $message
    */
   public static function onMessage($client_id, $data)
   {
       $class_dir = __DIR__ . "/Services"; //要读取的类文件路径
       $host_type = "internet"; //客户端类型
       echo  $_SERVER['REMOTE_ADDR']."\n";
       if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1")
       {
           echo "内网登陆";
           $class_dir = __DIR__ ."/SysServices";
           $host_type = "localhost"; //客户端类型
       }

       echo $_SESSION['UID']."\n";
       $statistic_address = 'udp://127.0.0.1:55656';
       // 判断数据是否正确
       if(empty($data['class']) || empty($data['method']) || !isset($data['param_array']))
       {
           // 发送数据给客户端，请求包错误
           if ($host_type == "internet" 
               && empty($_SESSION['UID']))   //未登录情况下，直接断开连接
           {
               $ret = Gateway::sendToCurrentClient(array('stat'=>100, 'text'=>'no login', 'data'=>null));
               Gateway::closeCurrentClient();
           }
           else
           {
               $ret = Gateway::sendToCurrentClient(array('stat'=>400, 'text'=>'bad request', 'data'=>null));
           }
           return $ret;
       }
       // 获得要调用的类、方法、及参数
       $class = $data['class'];
       $method = $data['method'];
       $param_array = $data['param_array'];

       //所有的调用一定要在登录后才可以
       if (
           (
               !empty($_SESSION['UID']) 
               || (
                   $class == "Device"
                   && $method == "Login"
               )
           )   
           || (
               $host_type == "localhost" //客户端类型
       )
   )
       {
           StatisticClient::tick($class, $method);
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
                   $code = 404;
                   $msg = "class $class not found";
                   StatisticClient::report($class, $method, $success, $code, $msg, $statistic_address);
                   // 发送数据给客户端 类不存在
                   //var_dump($connection);
                   return Gateway::sendToCurrentClient(array('stat'=>$code, 'text'=>$msg, 'data'=>null));
               }
           }

           // 调用类的方法
           try 
           {
               $ret = call_user_func_array(array($class, $method), array($client_id, $param_array));
               StatisticClient::report($class, $method, 1, 0, '', $statistic_address);
               // 发送数据给客户端，调用成功，data下标对应的元素即为调用结果
               return Gateway::sendToCurrentClient(array('stat'=>0, 'text'=>'ok', 'data'=>$ret));
           }
           // 有异常
           catch(Exception $e)
           {
               // 发送数据给客户端，发生异常，调用失败
               $code = $e->getCode() ? $e->getCode() : 500;
               StatisticClient::report($class, $method, $success, $code, $e, $statistic_address);
               return Gateway::sendToCurrentClient(array('stat'=>$code, 'text'=>$e->getMessage(), 'data'=>$e));
           }
       }
       else  //未登录直接断开操作
       {

           $ret = Gateway::sendToCurrentClient(array('stat'=>400, 'text'=>'no login', 'data'=>null));
           Gateway::closeCurrentClient();
           return $ret;
       }
   }
}

