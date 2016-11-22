<?php

namespace ws;

use swoole_websocket_server;

/**
 * Class Request
 * @package ws
 */
class Request {

	/** @var  swoole_websocket_server */
	public $server;
	public $frame;

	protected $data;

	/**
	 * Request constructor.
	 * @param  $server
	 * @param $frame
	 */
	public function __construct($server, $frame){
		$this->server = $server;
		$this->frame = is_array($frame) ? (object)$frame : $frame;
	}


	function getFd() {
		return $this->frame->fd;
	}

	function getData() {
		if(!isset($this->data)){
			$this->data = Packer::getInstance()->unpack($this->frame->data);
		}
		return $this->data;
	}

	function getVar($key, $def = null){
		if(!isset($this->data)){
			$this->getData();
		}

		if(isset($this->data[$key])){
			return $this->data[$key];
		} else {
			return $def;
		}
	}
}