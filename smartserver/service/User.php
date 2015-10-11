<?php
use \GatewayWorker\Lib\Db;
use \GatewayWorker\Lib\Gateway;
/**
 *  测试
 * @author walkor <worker-man@qq.com>
 */
class User
{
   public static function appLogin($client_id, $uid)
   {
       //验证是否已经登录
       if (!empty($_SESSION['UID']))
       {
           echo $client_id."已经登录\n";
       }
       else//处理登录逻辑
       {
           $_SESSION['UID'] = "ABC".$client_id."uid";
       }

       return array(
               'uid'    => $client_id,
               'session'=> $_SESSION['UID'],
               );
   }

   //测试Gateway转发功能
   public static function sendto($client_id, $uid)
   {
       Gateway::sendToClient($uid, "hello");

       return array(
               'uid'    => $uid,
               'session'=> $_SESSION['UID'],
               );
   }

   public static function getInfoByUid($client_id, $uid)
   {
       $_SESSION['UID'] = "ABC".$client_id."uid";
       return array(
               'uid'    => $uid,
               'name'=> 'test',
               'age'   => 18,
               'sex'    => 'hmm..',
               );
   }
   
   public static function getEmail($client_id, $uid)
   {
       $db = Db::instance('user'); //测试数据库操作
       var_dump($db->row("SELECT * FROM `net`"));
       return 'worker-man@qq.com';
   }
}
