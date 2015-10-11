<?php
/* 根据get表单的code值，
 * 从网页获取用户信息*/
require_once("common_inc.php");
require_once("libs/ezSQL_init.php");


try
{
    $post_data = file_get_contents("php://input"); //读取发来的json数据
    $_js = json_decode($post_data, true);
    if (is_array($_js)){
        parse_data($_js);
    }
    else{
        echo_error(10001);//JSON数据格式错误
    }
}
catch(Exception $e)
{
    echo_error(10003);//PHP异常
}



//回复错误信息
function echo_error($code){ 
    $arr = array("result"=>"ERROR", "code"=>$code);
    $json = json_encode($arr);
    echo $json;
}


//回复登录成功
function echo_success($fds){ 
    $arr = array("result"=>"OK", "code"=>10000, "fds"=>$fds);
    $json = json_encode($arr);
    echo $json;
}

//解析JSON数据
function parse_data($arr){ 
    if (array_key_exists("fid", $arr)
        && array_key_exists("uid", $arr))
    { //uid关键字是否存在
        $user_mn = new UserManager;

        if($user_mn->ModLogin($arr["uid"], $arr["fid"]))
        {
            $fds = $user_mn->GetUsersToLogout();

            //if (is_array($fds))
            //{
                ////foreach ($fds as $i)
                ////{
                    ////echo $i->fid."\n";
                ////}
            //}
            echo_success($fds);
        }
        else
        {
        echo $name." ".$value."\n";

            echo_error(20003);
        }
        
    }else{
        echo_error(10002); //缺少主要关键字
    }
}
//var_dump($var);



//$a = new ControlJSONRPC;

//var_dump($a->DisconnectClient($fid));

//echo "clos" ;

 
?>
