<?php

namespace ws\cmd;

use ws\Request;
use ws\Response;
use ws\Store;

class Message extends Base {

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return array|mixed
	 */
	function handle($request, $response){
		$fd = $request->getFd();
		$data = $request->getData();

		// 广播
		$private = false;
		$msg = $data['data'];
		if(isset($data['toUser']) && $data['toUser']){
			$private = true;
			if(Store::getInstance()->isOnline($data['toUser'])){
				$response->fd = $data['toUser'];
			} else {
				$msg = "User: {$data['toUser']} offline";
			}
		} else {
			$response = Response::createBroadcast($request);
		}

		$user = Store::getInstance()->getUserInfo($fd);
		$response->data = [
			"cmd" => 'message',
			"user" => $user['user'],
			"msg" => $msg,
			'private' => $private,
		];

		return $response;
	}
}