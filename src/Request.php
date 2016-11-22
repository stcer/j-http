<?php

namespace j\network\http;

use swoole_http_request as Base;

/**
 * Class Request
 * @package j\network\http
 *
 * @property array $header
 * @property array $server
 * @property array $post
 * @property array $get
 */
class Request{

    /**
     * @var Base
     */
    protected $request;

    /**
     * @param $request
     */
    function __construct($request) {
        $this->request = $request;
    }

    function __call($name, $arguments) {
        return call_user_func_array(array($this->request, $name), $arguments);
    }

    public function __get($key){
        return isset($this->request->{$key}) ? $this->request->{$key} : [];
    }

    public function __isset($key){
        return isset($this->request->{$key});
    }

    function getHeader($key = null, $def = null){
        if(!$key){
            return $this->request->header;
        }
        return isset($this->request->header[$key]) ? $this->request->header[$key] : $def;
    }

    function getUri(){
        return $this->request->server['request_uri'];
    }

    function getPath(){
        return $this->request->server['path_info'];
    }

    function getIp() {
        return $this->request->server['remote_addr'];
    }

    function getMethod(){
        return $this->request->server['request_method'];
    }

    public function isPost(){
        return $this->request->server['request_method'] == 'POST';
    }

    /**
     * @param null $key
     * @param null $def
     * @return array|mixed|null
     */
    function post($key = null, $def = null){
        if($key === null){
            return $this->post;
        }

        return isset($this->post[$key])
            ? $this->post[$key]
            : null;
    }

    /**
     * @param null $key
     * @param null $def
     * @return array|mixed|null
     */
    function get($key = null, $def = null){
        if($key === null){
            return $this->get;
        }

        return isset($this->get[$key])
            ? $this->get[$key]
            : $def;
    }

    /**
     * @param $key
     * @param null $def
     * @return mixed|null
     */
    public function request($key, $def = null){
        return isset($this->get[$key])
            ? $this->get[$key]
            :  (isset($this->post[$key])
                ? $this->post[$key]
                : $def)
            ;
    }
}