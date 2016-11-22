<?php

namespace j\network\http;

use swoole_http_response as Base;

/**
 * Class Response
 * @package j\network\http
 *
 * @method header($key, $value);
 * @method status($code);
 * @method end($content);
 */
class Response  {

    /**
     * @var Base
     */
    protected $response;

    function __construct($response) {
        $this->response = $response;
    }

    function __call($name, $arguments) {
        return call_user_func_array(array($this->response, $name), $arguments);
    }

    /**
     * @param $code
     * @return $this
     */
    function setHttpStatus($code) {
        $this->response->status($code);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    function setHeader($key, $value) {
        $this->response->header($key, $value);
        return $this;
    }

    function gzip($level = 1){
        $this->response->gzip($level);
        return $this;
    }

    public $body;

    /**
     * @param array $header
     * @return $this
     */
    function addHeaders(array $header) {
        foreach($header as $key => $value)
            $this->response->header($key, $value);
        return $this;
    }

    function headerAccessAllowDomain($domain = '*') {
        $this->response->header('Access-Control-Allow-Origin', $domain);
        return $this;
    }

    function headerContentType($type) {
        $this->response->header('Content-type', $type);
        return $this;
    }

    function noCache() {
        $this->response->header('CacheListener-Control',
            'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->response->header('Pragma','no-cache');
    }

    protected $isSend = false;

    /**
     * @param string $content
     */
    function send($content = ''){
        if($this->isSend){
            return;
        }

        $this->isSend = true;
        $this->response->end($content ? $content : $this->body);
    }

    public function isSend(){
        return $this->isSend;
    }

    /**
     * @param $rs
     */
    function sendJson($rs){
        $this->headerContentType('application/json');
        if (!is_string($rs)) {
            $rs = json_encode($rs);
        }

        $this->send($rs);
    }
}
