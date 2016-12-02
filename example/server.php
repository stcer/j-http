<?php

ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

$vendorPath = realpath(__DIR__ . "/../vendor/");
/** @var Composer\Autoload\ClassLoader  $loader */
$loader = include($vendorPath . '/autoload.php');

use j\network\http\Server;
use j\log\File as FileLog;
use j\log\Log ;
use j\network\http\Request;
use j\network\http\Response;
use Monolog\Logger;

// $log = new Log();
$log = new Logger("server");
$http = new Server('0.0.0.0', '5602');
$http->setOption([
    'ipc_mode' => 1,
    'max_request' => 10000,
    'log_file' => __DIR__ . '/swoole.log',
    ]);
$http->setLogger($log);
$http->dynamicParser = function(Request $request, Response $response, $server){
    $response->send("<h1>Hello word</h1>" . var_export($request, true));
};

$http->run();
