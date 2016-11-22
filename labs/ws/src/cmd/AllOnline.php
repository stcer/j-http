<?php

namespace ws\cmd;

use ws\Request;
use ws\Response;
use ws\Store;

class AllOnline extends Base {
	/**
	 * @param Request $request
	 * @param Response $response
	 * @return array|mixed
	 */
	function handle($request, $response){
		$users = Store::getInstance()->allOnline(true, $request->getFd());
		$response->data = [
			'cmd' => 'allOnline',
			'data' => $users
			];

		return $response;
	}
}