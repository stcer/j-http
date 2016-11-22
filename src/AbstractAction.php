<?php

namespace j\network\http;

//use j\event\TraitManager;

/**
 * Class AbstractAction
 * @package j\network\http
 */
abstract class AbstractAction{
    //use TraitManager;

    /**
     * @var Server
     */
    public $server;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;


    public $gzip = false;
    public $gzip_level = 1;

    /**
     * @var string
     */
    public $action = 'index';
    protected $accessAllow = '';

    /**
     * @param $rs
     */
    protected function response($rs){
        if ($this->gzip) {
            $this->response->gzip($this->gzip_level);
        }

        $this->response->end($rs);
    }

    /**
     * @param $rs
     */
    protected function responseJson($rs){
        // response
        $this->response->header('Content-type', 'application/json');
        if (!is_string($rs)) {
            $rs = json_encode($rs);
        }

        if($this->getVar('pretty')){
            $rs = JsonPretty::indent($rs);
        }

        if ($this->gzip) {
            $this->response->gzip($this->gzip_level);
        }

        if($this->accessAllow){
            $this->response->header('Access-Control-Allow-Origin', '*');
        }

        $this->response->end($rs);
    }

    /**
     * 添加http header
     * @param $header
     */
    protected function setHttpHead(array $header) {
        foreach($header as $key => $value)
            $this->response->header($key, $value);
    }

    const EVENT_ACTION_BEFORE = 'action.before';
    const EVENT_ACTION_AFTER = 'action.after';

    /**
     * @param Request $request
     * @param Response $response
     */
    function execute($request, $response){
        $actionName = $this->action . "Action";
        if(method_exists($this, $actionName)){
            //$this->trigger(self::EVENT_ACTION_BEFORE, $this, $request, $response);
            $this->request = $request;
            $this->response = $response;
            call_user_func(array($this, $actionName));
        } else {
            $response->status(500);
            $response->end("Invalid Action");
        }
    }

    /**
     * @param mixed $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * @param $key
     * @param null $def
     * @return array|mixed|null
     */
    protected function getVar($key, $def = null){
        return $this->request->get($key, $def);
    }

    /**
     * @param $key
     * @param null $def
     * @return array|mixed|null
     */
    protected function postVar($key, $def = null){
        return $this->request->post($key, $def);
    }
}