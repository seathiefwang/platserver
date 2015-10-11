<?php

include_once __DIR__.("/../db/Db.php");

class User
{
    //生成UUID
    public static function createUUID(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }

    //验证用户名
    public static function verifyUser($name){
        $plen=strlen($name);
        //'用户名必须为6-15位的数字和字母的组合';
        //if(!preg_match("/^(([a-z]+[0-9]+)|([0-9]+[a-z]+))[a-z0-9]*$/i",$name)||$plen<6||$plen>15){
        if($plen<6||$plen>15){
            return array("code"=>10004, "msg"=>"error", "ret"=>false);
        }
        else
        {
            return array("ret"=>true);
        }
    }

    //验证密码
    public static function verifyPasswd($passwd){
        //'密码必须为md5';
        if (!preg_match("/^[a-fA-F0-9]{32}$/", $passwd))  //验证密码是否为md5字符串
        {
            return array("code"=>10003, "msg"=>"不合法的密码格式", "ret"=>false);
        }
        else
        {
            return array("ret"=>true);
        }
    }

    //验证UUID
    public static function verifyUUID($uuid){
        //'必须为标准的UUID格式';
        //3080F1B1-4D74-E16F-0EFB-60BBBDFEA8E7
        if (!preg_match("/^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/", $uuid))  //验证密码是否为md5字符串
        {
            return array("code"=>10002, "msg"=>"UUID format error", "ret"=>false);
        }
        else
        {
            return array("ret"=>true);
        }
    }



    //用户注册
    //param:  $name --用户名
    //param:  $passwd --密码的MD5值
    //param:  $email --用户邮箱
    public static function regist($name, $passwd, $email)
    {
        if (!$email)
            return json_encode(array("code"=>10013, "msg"=>"no email"));
        $passwd = strtoupper($passwd);
        $ret = User::verifyUser($name); //验证用户名
        if ($ret["ret"])
        {
            $ret = User::verifyPasswd($passwd);
            if ($ret["ret"])  //验证密码
            {  //执行注册操作
                $db = Db::init();
                $users = $db->get_results("SELECT user FROM `user` where user = \"$name\"");
                if(isset($users[0]))
                {   //用户已经存在
                    return json_encode(array("code"=>10005, "msg"=>"error"));
                }
                else
                {
                    if($db->query("insert into `user`(`user`,`passwd`,`email`)values(\"$name\",\"$passwd\", \"$email\")")
                    )
                    {
                        //创建未分组房间
                        $db->query("insert into `user_room`(`user`,`name`)values(\"$name\",\"未分组\")");
                        $roomid = $db->get_var("select roomid from `user_room` where `user`=\"$name\";");
                        $db->query(" update user set room0id=$roomid where user=\"$name\"");
                        //注册成功
                        return json_encode(array("code"=>0, "msg"=>"ok"));
                    }
                }

                //服务器故障
                return json_encode(array("code"=>-1, "msg"=>"error"));
            }
        }
        return json_encode(array("code"=>$ret["code"], "msg"=>$ret["msg"]));
    }

    //登录
    //param:  $name --用户名
    //param:  $passwd --密码的MD5值
    public static function login($name, $passwd)
    {
        $passwd = strtoupper($passwd);
        $ret = User::verifyUser($name); //验证用户名
        if ($ret["ret"])
        {
            $ret = User::verifyPasswd($passwd);
            if ($ret["ret"])  //验证密码
            {  //执行登录
                $uuid =  USER::createUUID();

                $db = Db::init();
                $users = $db->get_results("SELECT user FROM `user` where user=\"$name\" and passwd=\"$passwd\"");

                $dev = array();
                $devices = $db->get_results("SELECT udid FROM `user_device` where user=\"$name\"");
                if (isset($devices[0]))
                {
                    foreach ($devices as $rec)
                    {
                        $dev[] = array("udid"=>$rec->udid);
                    }
                }

                //var_dump($devices);
                if(isset($users[0]))
                {   //用户密码正确
                    $uuid =  USER::createUUID();
                    if($db->query(" update user set uuid=\"$uuid\" where user=\"$name\""))
                    {
                        //登录成功
                        return json_encode(array("code"=>0, "msg"=>"ok",
                            "uuid"=>$uuid, "devices"=>$dev));
                    }
                    else
                    {
                        //服务器故障
                        return json_encode(array("code"=>-1, "msg"=>"error"));
                    }
                }
                else
                {
                    //登录用户名或密码错误
                    return json_encode(array("code"=>10001, "msg"=>"error"));
                }

                ////服务器故障
                //return json_encode(array("code"=>-1, "msg"=>"error"));
                //return json_encode(array("code"=>0, "msg"=>"ok", "uuid"=>$uuid));
            }
        }
        return json_encode(array("code"=>$ret["code"], "msg"=>$ret["msg"]));
    }

    //注销登录
    //param:  $name --用户名
    //param:  $passwd --密码的MD5值
    public static function logout($uuid)
    {
        $ret = USER::verifyUUID($uuid); //验证用户名
        if ($ret["ret"])
        {
            //echo "注销登录\n";
                $db = Db::init();
                $db->query("update user set uuid=\"\" where uuid=\"$uuid\"");
            return json_encode(array("code"=>0, "msg"=>"ok"));
        }
        return json_encode(array("code"=>$ret["code"], "msg"=>$ret["msg"]));
    }

    //验证uuid是否存在,是否有效登录
    //param:  $name --用户名
    //param:  $passwd --密码的MD5值
    public static function isUUIDexist($uuid)
    {
        $db = Db::init();
        $users = $db->get_results("SELECT user FROM `user` where uuid = \"$uuid\"");
        if(isset($users[0]))
        {   //用户已经有效登录
            return true;
        }
        else
        {
            return false;
        }

    }


    //设置用户信息
    //param:  $name --用户名
    //param:  $passwd --密码的MD5值
    public static function setInfo($name, $uuid, $info)
    {
        $ret = USER::verifyUUID($uuid); //验证uuid格式正确性
        if ($ret["ret"])
        {
            $db = Db::init();

                foreach($info as $key=>$value)
                {
                    switch ($key)
                    {
                    case "nickname":
                    case "email":
                    case "tel":
                    case "address":
                        $db->query("update user set $key=\"$value\" where uuid=\"$uuid\" and user=\"$name\"");
                        break;
                    }
                }

            //$users = $db->get_results("SELECT user FROM `user` where user = \"$name\"");

            return json_encode(array("code"=>0, "msg"=>"ok"));
        }
        //UUID格式错误
        return json_encode(array("code"=>$ret["code"], "msg"=>$ret["msg"]));
    }

    //获取用户信息
    //param:  $name --用户名
    //param:  $passwd --密码的MD5值
    public static function getInfo($name, $uuid)
    {
        $ret = USER::verifyUUID($uuid); //验证uuid格式正确性
        if ($ret["ret"])
        {
            $db = Db::init();

            $infos = $db->get_results("SELECT nickname,email,tel,address FROM `user` where uuid = \"$uuid\" and user=\"$name\"");
            if (isset($infos[0]))
            {
                foreach($infos[0] as $key=>$value)
                {
                    $info["$key"]=urlencode($value);
                }
            //$users = $db->get_results("SELECT user FROM `user` where user = \"$name\"");

                return urldecode(json_encode(array("code"=>0, "msg"=>"ok", "info"=>$info)));
            }
            return json_encode(array("code"=>11005, "msg"=>"uuid is not this user's"));
        }
        //UUID格式错误
        return json_encode(array("code"=>$ret["code"], "msg"=>$ret["msg"]));
    }

    //修改密码
    //param:  $name --用户名
    //param:  $uuid --用户登录后的UUID
    //param:  $passwd --密码的字段 array("oldpasswd"=>value, "newpasswd"=>value)
    public static function chpasswd($name, $uuid, $passwd)
    {
        if (is_array($passwd)
            &&isset($passwd["oldpasswd"])
            &&isset($passwd["newpasswd"])
        )
        {
            $newpasswd = $passwd["newpasswd"];
            $oldpasswd = $passwd["oldpasswd"];
            $ret = User::verifyPasswd($newpasswd);
            if ($ret["ret"])  //验证新密码格式
            {  //执行注册操作
                $db = Db::init();

                if($db->query("update user set passwd=\"$newpasswd\" where user=\"$name\" and passwd=\"$oldpasswd\""))
                {
                        return json_encode(array("code"=>0, "msg"=>"ok"));
                }
                else
                {
                        return json_encode(array("code"=>10012, "msg"=>"old password error"));
                }
            }
            else
            {
                return json_encode(array("code"=>$ret["code"], "msg"=>$ret["msg"]));
            }

        }
        else
        {
                //密码字段不完整
                return json_encode(array("code"=>-1, "msg"=>"error"));
        }
    }

    public static function setPriv($uuid, $user, $priv)
    {
        $uuid = strtoupper ($uuid);
        $db = Db::init();
        if ($priv >= 3) {
                return json_encode(array("code"=>10015, "msg"=>"权限参数不合法"));
        }
        $me = $db->get_results("select priv,user from user where uuid=\"$uuid\"");
        if ($me[0]->priv == 3 && $me[0]->user!=$user)//超级管理员
        {
            if($db->query("select user from user where user='$user'"))//用户存在
            {
                //$db->query("insert into `user_relation`(`admin`,`user`,`priv`)values(\"".$me[0]->user."\",\"$user\", $priv)");
                $db->query("update user set priv=\"$priv\", admin=\"".$me[0]->user."\" where user=\"$user\" ");
                return json_encode(array("code"=>0, "msg"=>"ok"));
                
            }
            else
            {
                return json_encode(array("code"=>10014, "msg"=>"被授权的用户不存在"));
            }
        }
        else
        {
                return json_encode(array("code"=>10016, "msg"=>"您没有权限进行该项操作。"));
        }
        return json_encode(array("code"=>-1, "msg"=>"error"));
    }

//获得验证码
    public static function getVerCode($user)
    {
        $db = Db::init();
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = substr(rand()."", 0, 8);
            $email = $db->get_var("select email from user where user=\"$user\"");
            $db->query("update user set pwcode=\"_$charid\" where user=\"$user\" ");

            return json_encode(array("code"=>0, "msg"=>"0", "email"=>$email));
    }
//核对验证码
    public static function verifyVerCode($user, $vercode)
    {
        $db = Db::init();
        $voucher =  USER::createUUID();//凭证
        if ($db->query("update user set pwvoucher=\"_$voucher\" where user=\"$user\" and pwcode=\"_$vercode\";"))
        {
            return json_encode(array("code"=>0, "msg"=>"ok", "voucher"=>$voucher));
        }
            return json_encode(array("code"=>10017, "msg"=>"验证码不匹配"));
    }
//重置密码
    public static function pwReset($user, $voucher, $passwd )
    {
        $passwd = strtoupper($passwd);
        $ret = User::verifyPasswd($passwd);
        if ($ret["ret"])  //验证新密码格式
        {  //执行注册操作
            $db = Db::init();

            if($db->query("update user set passwd=\"$passwd\",pwvoucher=\"\", pwcode=\"\" where user=\"$user\" and pwvoucher=\"_$voucher\""))
            {
                return json_encode(array("code"=>0, "msg"=>"ok"));
            }
            else
            {
                return json_encode(array("code"=>10018, "msg"=>"修改密码凭证有误。"));
            }
        }
        else
        {
            return json_encode(array("code"=>$ret["code"], "msg"=>$ret["msg"]));
        }
    }

}
?>
