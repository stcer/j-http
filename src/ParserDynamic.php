<?php

namespace j\network\http;

use Exception;
use j\log\TraitLog;

/**
 * Class ParserDynamic
 * @package j\network\http
 */
class ParserDynamic {

    use TraitLog;

    public $actionNs = '';

    /**
     * @var Server|Protocol
     */
    private $server;

    /**
     * ParserDynamic constructor.
     * @param Server|Protocol $server
     */
    public function __construct($server) {
        $this->server = $server;
    }


    /**
     * @param $request
     * @param $response
     * @param $action
     * @throws Exception
     */
    public function __invoke($request, $response, $action) {
        if(!preg_match('/^[a-zA-Z_\/]+$/', $action)){
            throw new Exception("Invalid cgi request, bad char");
        }

        $actionClass = $this->loadActionClass($action);
        if(!$actionClass){
            throw new Exception("Invalid cgi request, action not found");
        }

        $actionClass->server = $this->server;
        $actionClass->execute($request, $response);
    }

    /**
     * @param $action
     * @return AbstractAction
     */
    protected function loadActionClass($action){
        $actions = explode("/", $action);
        $ctrl = array_shift($actions);

        $class =  $this->actionNs  . ucfirst($ctrl);
        $this->log("Load action class: " . $class, 'debug');

        if(!class_exists($class)){
            return null;
        }

        /** @var AbstractAction $instance */
        $instance = new $class();
        if($actions){
            $method = array_shift($actions);
            $this->log("Method: " . $method, 'debug');
            $instance->setAction($method);
        }

        return $instance;
    }
}