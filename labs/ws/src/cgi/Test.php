<?php

namespace ws\cgi;

use j\network\http\AbstractAction;
use j\network\http\Response;
use ws\Store;

class Test extends AbstractAction{
	function indexAction(){
		/** @var \Swoole\WebSocket\Server $server */
		$server = $this->server->getSwoole();
		$fds = Store::getInstance()->allOnline();
		foreach($fds as $fd){
			$server->push(
				$fd, json_encode([
				"cmd" => 'message',
				"msg" => "hello world1",
				'user' => '超级Bug',
				"fd" => $server->worker_id
				])
			);
		}

		$data = "<h1>管理员向所有人通知</h1>";
		$data .= var_export($fds, true);
		$this->response($data);
	}
}