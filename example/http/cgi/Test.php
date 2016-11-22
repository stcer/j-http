<?php

namespace cgi;

use j\network\http\AbstractAction;
use j\network\http\Response;

class Test extends AbstractAction {

    function indexAction(){
        $timeStart = time();
        $n = rand(1, 1000);

        $this->server->task(
            "sendMail",
            ["address", "mail subject", "mail_body" . $n],
            function($rs, $params) {
                echo "rs: {$rs} ";
                echo "log in action\n";
            }
        );

        $this->server->task(
            "taskInTask",
	        [],
            function($rs) {
                echo "rs: {$rs} ";
                echo "log in action2\n";
            }
        );

        // 异步执行任务
        $this->server->tasks([
            ['query', ["select1 需求花2秒"]],
            ['sendMail', ["address", "mail subject", "发邮件需要花1秒"]],
            ['query', ["select2 需求花2秒"]],
        ], function($data, Response $response) use ($n, $timeStart){
            // 执行响应客户端
            $times = time() - $timeStart;
            $rs = "query1 " . var_export($data[0], true) . "\n";
            $rs .= "sendMail " . var_export($data[1], true) . "\n";
            $rs .= "query2 " . var_export($data[2], true) . "\n";

            $html = <<<STR
<h1>并行运行测试 {$n}</h1>
<pre>{$rs}</pre>
页面请求时间：$times
STR;
            $response->headerContentType("text/html;charset=utf-8");
            $response->send($html);
        }, $this->response);
    }
}