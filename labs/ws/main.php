<?php

require __DIR__ . "/boot.inc.php";

use ws\ImServer;
use j\log\Log;

$server = new ImServer('0.0.0.0', 9503);
$server->documentRoot = __DIR__ . "/src/client/";
$server->actionNs = 'ws\\cgi\\';
$server->setLogger(new Log());
$server->setOption([
//	'heartbeat_check_interval' => 5,
//	'heartbeat_idle_time' => 10,
	'open_tcp_keepalive' => 1,
	'tcp_keepidle' => 10,
	'tcp_keepcount' => 2,
	'tcp_keepinterval' => 5,
	]);

$server->run();