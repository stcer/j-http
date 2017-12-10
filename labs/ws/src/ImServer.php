<?php

namespace ws;

use swoole_websocket_server;
use j\network\http\Server;

/**
 * Class ImServer
 * @package ws
 */
class ImServer extends Server{
    /**
     * @return swoole_websocket_server
     */
    protected function createServer() {
        return new swoole_websocket_server($this->ip, $this->port);
    }

	/**
	 * @param \swoole_http_server $http
	 */
	protected function onServerCreate($http){
		parent::onServerCreate($http);
		$this->setOption('dispatch_mode', 2);
	}

	/**
     * @param \swoole_http_server $server
     */
    protected function bindEvent($server) {
	    // bind other event
        parent::bindEvent($server);

	    // bind web socket event
        $protocol = $this->getProtocol();
        $protocol->server = $this;
        $binds = [
            'onOpen' => 'Open',
            'onMessage' => 'message',
            'onClose' => 'Close',
            'onServerStart' => 'ManagerStart',
        ];
        foreach($binds as $method => $evt){
            if(method_exists($protocol, $method)){
                $server->on($evt, array($protocol, $method));
            }
        }
    }

	/**
     * @var Protocol
     */
    protected $protocol;


    /**
     * @param $protocol
     * @throws \Exception
     */
    function setProtocol($protocol){
        $this->protocol = $protocol;
    }

    /**
    * @return Protocol
    * @throws \Exception
    */
    public function getProtocol() {
        if(!isset($this->protocol)){
            $this->protocol = new Protocol();
	        $this->protocol->setLogger($this->getLogger());
        }

        if(is_string($this->protocol)){
            $this->protocol = new $this->protocol();
            $this->protocol->setLogger($this->getLogger());
        }

        return $this->protocol;
    }
}
