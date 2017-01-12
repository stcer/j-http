<?php

$vendorPath = realpath(__DIR__ . "/../../vendor/");
$loader = include($vendorPath . "/autoload.php");

$loader->add("cgi", __DIR__);
$loader->register();

$ip = '0.0.0.0';
$port = '8080';
$ssl = __DIR__ . '/ssl/';
$ssl = false;

$server = new j\network\http\Server($ip, $port, $ssl);
$server->setOption('task_worker_num', 10);

// 注册任务
$server->regTasks([
    "sendMail" => function($address, $title, $body){
        var_dump($address);
        return "send mail to {$address} with '{$title} {$body}'";
        },
    "query" => function($sql){
        sleep(2);
        return "query '{$sql}' result";
        },
    "taskInTask" => function() use($server){
        $data = null;
	    echo "Task run task\n";
        $server->task('sendMail', ["task", "task", "taskInfo"], function($rs) use(& $data){
            $data = $rs;
        });
        return $data;
    }
]);

$server->documentRoot = __DIR__ . "/www";
$server->actionNs = 'cgi\\';

$server->setLogger(new j\log\Log());
$server->setOption('pid_file', __DIR__ . "/pid");
//$server->setOption('daemonize', 1);
$server->run();