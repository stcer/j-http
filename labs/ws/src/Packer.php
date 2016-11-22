<?php

namespace ws;

/**
 * Class Packer
 * @package ws
 */
class Packer {

	private static $instance;

	/**
	 * @return Packer
	 */
	public static function getInstance(){
		if(!isset(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @param $data
	 * @return string
	 */
	function pack($data){
		$data['date'] = date('H:i:s');
		return json_encode($data);
	}

	function unpack($message) {
		return json_decode($message, true);
	}
}