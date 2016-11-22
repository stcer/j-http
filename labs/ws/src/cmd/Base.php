<?php

namespace ws\cmd;

use ws\Response;
use ws\Request;
use ws\Store;
use ws\Packer;

/**
 * Class Cmd
 * @package ws\cmd
 */
abstract class Base {
	/**
	 * @param Request $request
	 * @param Response $response
	 * @return array|mixed
	 */
	abstract function handle($request, $response);
}