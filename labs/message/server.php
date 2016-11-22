<?php

use j\network\message\Server;
use j\log\Log;

$vendorPath = realpath(__DIR__ . "/../../vendor/");
$loader = include($vendorPath . "/autoload.php");
$loader->addPsr4("j\\network\\message\\", __DIR__ . '/src/message');

$log = new Log();
$log->setMask(Log::ERROR);

$server = new Server('0.0.0.0', '8060');
$server->setLogger($log);
//$server->daemonize();
$server->setOption([
    'worker_num' => 10,
    'max_request' => 1,
    'task_worker_num' => 2,
    'task_ipc_mode' => 3,
    'pid_file' => __DIR__ . "/swoole_yar_server.pid",
    ]);

$server->run();
