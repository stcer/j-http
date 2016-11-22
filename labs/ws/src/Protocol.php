<?php

namespace ws;

use swoole_websocket_server;
use Exception;
use j\log\TraitLog;

/**
 * Class Protocol
 * @package ws
 */
class Protocol {

    use TraitLog;

    /**
     * @var ImServer
     */
    public $server;

	/**
	 * @var string
	 */
	public $cmdNs = 'ws\\cmd\\';

	/**
	 * @param $serv
	 */
	function onServerStart($serv){
		$this->server->onServerStart($serv);
		Store::getInstance()->clearOnline();
	}

    /**
     * @param swoole_websocket_server $server
     * @param $req
     */
    function onOpen($server, $req) {
	    $connInfo = $server->connection_info($req->fd);
	    $loginInfo = [
	    	'ip' => $connInfo['remote_ip'],
		    'fd' => $req->fd,
		    'user' => $connInfo['remote_ip'] . '::' . $req->fd,
	        ];
    	Store::getInstance()->addOnline($req->fd, $loginInfo);
        $this->log("connection open: ". $req->fd);

		// 登录状态
	    $request = new Request($server, ['fd' => $req->fd]);
	    $response = new Response($request);
	    $response->data = [
		    'cmd' => 'login',
		    'user' => $loginInfo
	        ];
	    $this->response($response);

	    // 广播用户上线
	    $response = Response::createBroadcast($request);
	    $response->data = [
	    	'cmd' => 'online',
		    'user' => $loginInfo
            ];
	    $this->response($response);
    }

	/**
	 * @param swoole_websocket_server $server
	 * @param $fd
	 */
	function onClose($server, $fd) {
		$user = Store::getInstance()->getUserInfo($fd, true);
		if(!$user){
			return;
		}

		Store::getInstance()->delOnline($fd);
		$this->log("connection close: ".$fd);

		// 广播用户下线
		$response = Response::createBroadcast(new Request($server, ['fd' => $fd]));
		$response->data = [
			'cmd' => 'offline',
			'user' => $user
			];
		$this->response($response);
	}

    /**
     * @param swoole_websocket_server $server
     * @param $frame
     */
    function onMessage($server, $frame) {
	    $request = new Request($server, $frame);
	    $response = new Response($request);
        try {
            $data = $request->getData();
	        $this->log($data);

	        $cmd = ucfirst($request->getVar('cmd'));
	        $cmd = trim($cmd, '.');
	        $cmd = str_replace('.', '\\', $cmd);
	        $cmd =  $this->cmdNs . $cmd;

	        if(!class_exists($cmd)){
	        	throw new Exception("Invalid cmd:{$cmd}");
	        }

	        /** @var cmd\Base $cmdInstance */
	        $cmdInstance = new $cmd();
	        $rs = $cmdInstance->handle($request, $response);
	        if(is_array($rs)) {
		        foreach($rs as $res) {
			        $this->response($res);
		        }
	        } elseif($rs instanceof Response) {
		        $this->response($rs);
	        } else {
		        $this->response($response);
	        }
        } catch (Exception $e){
	        $response->data = [
		        "cmd" => "error",
		        "msg" => $e->getMessage() . "(" . $e->getCode() . ")"
	            ];
	        $this->response($response);
        }
    }

	/**
	 * @param Response $response
	 */
    protected function response($response){
    	if($response instanceof response\Broadcast)
		    $this->broadcastJson($response->data, $response->fd);
	    elseif($response->data){
	    	$this->send($response->fd, $response->data);
	    }
    }

	/**
	 * @param $fd
	 * @param $data
	 */
	protected function send($fd, $data){
		/** @var \Swoole\WebSocket\Server $server */
		$server = $this->server->getSwoole();
		$server->push($fd, Packer::getInstance()->pack($data));
	}

	/**
	 * 广播JSON数据
	 * @param $data
	 * @param $meFd
	 */
	protected function broadcastJson($data, $meFd){
		$fds = Store::getInstance()->allOnline();
		foreach($fds as $fd){
			if($meFd == $fd){
				continue;
			}
			$this->send($fd, $data);
		}
	}
}