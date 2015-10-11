<?php
/* 根据get表单的code值，
 * 从网页获取用户设备信息*/
//require_once("common_inc.php");
//require_once("libs/weichat_api.php");
//require_once("libs/ezSQL_init.php");


header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache'); 



?>


<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-store, must-revalidate">
    <meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css">
    <script src="/js/jquery-1.8.3.min.js"></script>
    <script src="/js/jquery.mobile-1.3.2.min.js"></script>
    </script>
        <title>您的设备列表</title>
  </head>
  <body>


    <div data-role="page" id="pageone">
      <div data-role="content">
        <ul data-role="listview" id="dev_list_ul" data-inset="true">
          <li data-role="divider">您的设备列表</li>
          


        </ul>
      </div>
    </div> 


    <!--+设备设置对话框-->
    <div data-role="page" id="setup">
      <div data-role="content">
        <h3>设备名称设置</h3>
        <!--<form id="devsetup" class="validate" action="javascript:void(0);">-->
        <form id="devsetup" action="#">
          <div data-role="fieldcontain">
            <label for="setup_device_id">设备ID：</label>
            <input type="text" readonly=true name="device_id" id="setup_device_id">
            <label for="setup_devicename">设备名：</label>
            <input type="text" name="devicename" id="setup_devicename">       
            <label for="setup_childtotal">子设备数量(1～20)：</label>
            <input type="text" name="childtotal" id="setup_childtotal">      
          </div>
          <input type="submit" data-inline="true" value="提交修改">
          <a data-role="button" data-rel="back" data-inline="true">取消</a>
        </form>
      </div> 
    </div>
    <!---设备设置对话框-->


  </body>
</html>



<?php

//设备列表
    function device_item($id, $name, $slave, $openid){
        $a=<<<EOD
          <li id="dev_list_$id">
          <a href="childlist.php?deviceid={$id}&openid={$openid}" data-transition="slide" >
            <h2 id="dev_list_{$id}_name">$name</h2>
            <p >设备ID: <span id="dev_list_{$id}_id" >{$id}</span> &nbsp;子设备数:<span id="dev_{$id}_slave_total" >{$slave}</span></p>
            <!--<a href="#setup_{$id}" data-rel="dialog" data-transition="slide" onclick="init_setup_diag('{$id}')">设置</a>-->
            <!--<a href="childlist.php?deviceid={$id}&openid={$openid}" data-transition="slide">设置</a>-->
            <a href="setdevice.php?deviceid={$id}&openid={$openid}&devicename={$name}&slavetotal={$slave}" data-transition="slide">设置</a>
          </a>
          </li>
EOD;
        return $a;
    }

    //设备设置
    function device_set($id, $name, $slave_total){
    $a=<<<EEEE
    <!--+设备设置对话框-->
    <div data-role="page" id="setup_{$id}">
      <div data-role="content">
        <h3>设备名称设置</h3>
        <form id="devsetup" action="javascript:void(0);">
          <div data-role="fieldcontain">
            <label for="setup_{$id}_id">设备ID：</label>
            <input type="text" readonly=true name="device_id" id="setup_{$id}_id", value="{$id}">
            <label for="setup_devicename_{$id}">设备名：</label>
            <input type="text" name="devicename" id="setup_devicename_{$id}" value="{$name}">       
            <label for="setup_childtotal_{$id}">子设备数量(1～20)：</label>
            <input type="text" name="childtotal" id="setup_childtotal_{$id}" value="{$slave_total}">      
          </div>
          <input type="submit" data-inline="true" value="提交修改">
          <a data-role="button" data-rel="back" data-inline="true">取消</a>
        </form>
      </div> 
    </div>
    <!---设备设置对话框-->
EEEE;
    return $a;
    }

//子设备列表 $id --设备ID  $name --设备名 $num --子设备序号
function child_list($id, $name, $total){
    $content="";
    $start=<<<EOD
    <div data-role="page" id="childlist_{$id}">
      <div data-role="header">
        <h1>{$name}子设备列表</h1>
      </div>
      <div data-role="content">
        <div data-role="collapsible-set">
EOD;

    global $db;

    $arr = $db->get_results("select NO,name from dp_info where deviceid='".$id."' order by NO");
    //var_dump($arr);

    if(is_array($arr)){
    //foreach($arr as $i){
    for ($i=0; $i<$total; $i ++){
        if ($arr[$i]->name==null) $arr[$i]->name="模块".$arr[$i]->NO;
        $content.=<<<EOD
          <form method="post" class="validate" action="javascript:void(0);" onsubmit="set_child_name(this,'{$id}', {$arr[$i]->NO})">
            <fieldset data-role="collapsible">
              <legend>{$arr[$i]->name}</legend>
              <label for="name">备注名：</label>
              <input type="text" name="name" id="name" value="{$arr[$i]->name}">
              <input type="submit" data-inline="true" value="提交">
            </fieldset>
          </form>
EOD;
    }
    }


    $footer=<<<EOD
        </div>
      </div>
    </div>
EOD;
    return $start.$content.$footer;
}


?>
