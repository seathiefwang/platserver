<?php
//require_once("common_inc.php");
//require_once("libs/ezSQL_init.php");

class UserManager extends ControlJSONRPC
{

    private $db;
    private $fids_arr; //已经登录的UID对应的fid
    function __construct()
    {
        $this->db = $GLOBALS['db'];
    }

    function __destruct()
    {
    }

    /**
     * 模块登录
     * 
     * @param uid
     */
    public function ModLogin($uid, $fid)
    {

        //验证登录的设备ID是否已经登录
        $this->fids_arr = $this->db->get_results("SELECT * FROM net where `uid`=\"$uid\"");
        if (is_array($this->fids_arr))
        {
            foreach ($this->fids_arr as $i)
            {
                //var_dump($i->fid);
                //echo $i->fid."\n";

                //ControlJSONRPC::DisconnectClient($i->fid);

            }
            $this->db->query ("delete from net where `uid`=\"$uid\"");
        }

        //在登录的用户列表中插入该用户
        if($this->db->query("insert into net(fid, uid) values ($fid, \"$uid\")"))
        {
            return true;
        }

    $arr = array("result"=>$uid, "fid"=>$fid);
    $json = json_encode($arr);
    echo $json;
        return false;
    }
    public function GetUsersToLogout()
    {
        return $this->fids_arr;
    }

    //给指定用户发送text数据
    public function SendTextToUser($from, $uid, $cont)
    {
        $_ret = "{\"result\":\"error\"}";
        $user_fd = $this->db->get_results("SELECT fid FROM net where `uid`=\"$uid\"");
        if ($user_fd)
        {
            $fid = $user_fd[0]->fid;
            //echo $fid;
            $_ret = ControlJSONRPC::SendTextToFid($from, $uid, $fid*1, $cont);
        }
        return $_ret;
    }

    //模块断开登录
    public function ModLogout($fid)
    {
        $this->db->query ("delete from net where `fid`=$fid");
    }
}

?>
