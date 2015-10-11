<?php

if (isset($_POST["ssid"])
    && isset($_POST["key"])
    && isset($_POST["psk"])
    )
{
    $ssid = $_POST["ssid"];
    $key  = $_POST["key"];
    $psk  = $_POST["psk"];

    //system ("{ sleep 5; /usr/bin/wiset.sh '$ssid' '$key' '$psk'; }  1>/dev/console 2>/dev/console &");
    system ("{ sleep 5; /usr/bin/wiset.sh '$ssid' '$key' 'psk$psk'; }  1>/dev/null 2>/dev/null &");
    //system ("/usr/bin/wiset.sh ");
    //echo json_encode(array("code"=>0, "msg"=>"ok"));
}
else
{
    //echo "eooro";
}

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="Cache-Control" content="no-store, must-revalidate">
        <meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT">
        <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css">
        <script src="js/jquery-1.8.3.min.js"></script>
        <script src="js/jquery.mobile-1.3.2.min.js"></script>
        <title>无线局域网连接</title>
    </head>
    <body>

        <div data-role="page" id="pagetwo">
            <div data-role="header">
                <h1>一切准备就绪!</h1>
            </div>

            <div data-role="content">
                <p>主机将在5s后切换成STA模式，连接您选定的WiFi网络。绿灯闪烁就表明主机开始正常工作了!</p>
            </div>

            <div data-role="footer">
                <h1></h1>
            </div>
        </div> 

</body>
</html>
