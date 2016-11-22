<?php

namespace j\network\https;

use swoole_http_request;
use swoole_http_response;
use Exception;

/**
 * Class TraitAction
 * @package j\network\https
 */
abstract class TraitAction{

    protected function gav($array, $key, $def = null){
        if(is_array($array)){
            return array_key_exists($key, $array) ? $array[$key] : $def;
        }else if(is_object($array)){
            return $array[$key] ? $array[$key] : $def;
        } else {
            throw(new Exception('arg 1 is not a array'));
        }
    }

    /**
     * @param $rs
     * @param swoole_http_response $response
     */
    protected function sendJson($rs, $response){
        // response
        $response->header('Content-type', 'application/json');
        if (!is_string($rs)) {
            $rs = json_encode($rs);
        }
        $response->end($rs);
    }

    public function setAction($action){
	    $this->action = $action;
    }

    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     */
    abstract function execute($request, $response);

}