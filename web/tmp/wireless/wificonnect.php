<?php
$json_string = file_get_contents("php://input"); 

$root = json_decode($json_string, true);

if ($root 
    && isset($root["ssid"])
    && isset($root["key"])
    && isset($root["psk"])
    )
{
    $ssid = $root["ssid"];
    $key  = $root["key"];
    $psk  = $root["psk"];

    system ("{ sleep 5; wiset.sh; }  1>/dev/console 2>/dev/console &");
    //system ("/usr/bin/wiset.sh ");
    echo json_encode(array("code"=>0, "msg"=>"ok"));
}

?>
