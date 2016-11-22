<?php

namespace ws;

/**
 * Class Response
 * @package ws
 */
class Response {

	public $fd = 0;
	public $data = [];

	/**
	 * @var Request;
	 */
	public $request;

	/**
	 * @param $request
	 * @return static;
	 */
	static function createBroadcast($request){
		$object = new response\Broadcast($request);
		return $object;
	}

	/**
	 * Response constructor.
	 * @param Request $request
	 */
	public function __construct(Request $request){
		$this->request = $request;
		$this->fd = $this->request->getFd();
	}
}