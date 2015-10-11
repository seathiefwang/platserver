<?php
/**
 * run with command 
 * php start.php start
 */

ini_set('display_errors', 'on');
use Workerman\Worker;

require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__.'/Applications/Todpole/start.php';
// require_once __DIR__.'/Applications/JsonRpc/start.php';

Worker::runAll();
