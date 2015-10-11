<?php
//use Workerman\Worker;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \GatewayWorker\Lib\Db;

require_once (__DIR__ . "/Config/Db.php");

$db = Db::instance('user'); //清理登录记录
$db->query ("truncate table `device_login`");

// gateway
$gateway = new Gateway("JsonNL://0.0.0.0:2016");
$gateway->name = 'JsonRPCGateway';
$gateway->count = 2;
$gateway->lanIp = '127.0.0.1';
$gateway->startPort = 4000;
$gateway->pingInterval = 30;
$gateway->pingNotResponseLimit = 2;
$gateway->pingData = array("method"=>"ping");

$gateway = new Gateway("IBeeAir2://0.0.0.0:2018");
$gateway->name = 'JsonRPCGateway';
$gateway->count = 2;
$gateway->lanIp = '127.0.0.1';
$gateway->startPort = 4000;
$gateway->pingInterval = 30;
$gateway->pingNotResponseLimit = 2;
$gateway->pingData = array("method"=>"ping");

$inner_gateway = new Gateway("JsonNL://127.0.0.1:2017");
$inner_gateway->name = 'JsonRPCInnerGateway';
$inner_gateway->count = 2;

// bussinessWorker
$worker = new BusinessWorker();
$worker->name = 'JsonRPCBusinessWorker';
$worker->count = 2;

