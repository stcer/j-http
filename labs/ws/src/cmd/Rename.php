<?php

namespace ws\cmd;

use ws\Request;
use ws\Response;
use ws\Store;

class Rename extends Base {

	/**
	 * @param Request $request
	 * @param Response $response
	 * @return array|mixed
	 */
	function handle($request, $response){
		$fd = $request->getFd();
		$userInfo = Store::getInstance()->getUserInfo($fd);
		if(!$userInfo){
			return [];
		}

		$oldName = $userInfo['user'];
		$userName = $request->getVar('name');
		if($userName){
			$userInfo['user'] = $userName;
		}

		Store::getInstance()->saveUserInfo($fd, $userInfo);

		// 广播
		$response = Response::createBroadcast($request);
		$response->fd = 0;
		$response->data = [
			"cmd" => 'rename',
			"user" => $userInfo['user'],
			"fd" => $fd,
			"ip" => $userInfo['ip'],
			"msg" => "{$oldName} rename to {$userInfo['user']}",
		];

		return $response;
	}
}