<?php

namespace j\network\http;

use Swoole\Http\Request as SRequest;
use Swoole\Http\Response as SResponse;
use swoole_http_request;
use swoole_http_response;

use j\log\TraitLog;
use Exception;
use Closure;

/**
 * Class Protocol
 * @package j\network\http
 */
class Protocol {
    use TraitLog;

    /**
     * @var string
     */
    public $documentRoot;
    public $cgiPathPrefix = '/cgi/';
    public $defaultIndex = 'index.html';


    /**
     * @var ParserStatic
     */
    public $staticParser;

    /**
     * @var bool
     */
    public $basicAuth = false;

    /**
     * @var ParserDynamic|Closure
     */
    public $dynamicParser;

    /**
     * @var string
     */
    public $actionNs;

    /**
     * @var Server
     */
    protected $server;

    /**
     * Protocol constructor.
     * @param Server $server
     */
    public function __construct(Server $server) {
        $this->server = $server;
    }

    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     */
    public function onRequest($request, $response){
        $path = $request->server['request_uri'];
        $this->log("Request:" . $request->server['request_uri'], 'debug');

        // todo trigger event
        if($this->isDynamicRequest($path, $request)){
            $this->parseDynamicRequest($path, $request, $response);
        } else {
            // client
            if (!$path || substr($path, -1) == '/') {
                $path = $path . $this->defaultIndex;
            }
            $this->getStaticParser()->execute($request, $response, $path);
        }
    }


    /**
     * DynamicParser 可设置为不同的http协议，可完全重写此方法
     *
     * @param string $path
     * @param SRequest $request
     * @param SResponse $response
     */
    protected function parseDynamicRequest($path, $request, $response){
        try{
            $res = new Response($response);
            $action = substr($path, strlen($this->cgiPathPrefix));
            $data = call_user_func(
                $this->getDynamicParser(),
                new Request($request),
                $res,
                $action
            );
            if($res->isSend() || !$data){
                return;
            }

            if(is_string($data)){
                $res->send($data);
            } else {
                $res->sendJson($data);
            }
        } catch(Exception $e) {
            $message = $e->getCode() . ":" . $e->getMessage();
            $response->status(500);
            $response->end($message);
            $this->log($message . $e->getTraceAsString(), 'error');
        }
    }

    /**
     * 是否动态请求，可完全重写此方法
     * @param $path
     * @param $request
     * @return bool
     */
    protected function isDynamicRequest($path, $request) {
        return strpos($path, $this->cgiPathPrefix) === 0;
    }

    /**
     * @return ParserDynamic
     */
    public function getDynamicParser() {
        if(!isset($this->dynamicParser)){
            $dynamicParser = new ParserDynamic($this->server);

            if($log = $this->getLogger()){
                $dynamicParser->setLogger($log);
            }

            if(isset($this->actionNs)){
                $dynamicParser->actionNs = $this->actionNs;
            }

            $this->dynamicParser = $dynamicParser;
        }
        return $this->dynamicParser;
    }

    /**
     * @return ParserStatic
     */
    public function getStaticParser() {
        if(!isset($this->staticParser)){
            $this->staticParser = new ParserStatic();

            if(isset($this->documentRoot)){
                $this->staticParser->setDocumentRoot($this->documentRoot);
            }
        }
        return $this->staticParser;
    }

    /**
     * @param $request
     * @param $response
     * @return bool
     */
    protected function authorization($request, $response){
        if(!$this->basicAuth){
            return true;
        }

        $httpParser = $this->getStaticParser();
        if($httpParser->authorization($request, $response, "authorization")){
            return true;
        }

        return false;
    }
}