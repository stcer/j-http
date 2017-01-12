<?php

namespace j\network\https;

use swoole_http_server;
use swoole_http_request;
use swoole_http_response;

use j\log\TraitLog;
use Exception;

/**
 * Class Server
 * @package j\network\https
 */
class Server {
    use TraitLog;

    /**
     * @var string
     */
    public $sslKeyPath;
    public $ip = '0.0.0.0';
    public $port = '9443';
    protected $setting = [
        'worker_num' => 2,
    ];

    /**
     * @var string
     * require
     */
    public $documentRoot;
    public $actionNs = '';

    public $defaultIndex = 'index.html';
    public $cgiPathPrefix = '/cgi/';

    /**
     * @var HttpParser
     */
    protected $httpParser;

    /**
     * @param string $ip
     * @param string $port
     */
    function __construct($ip = '0.0.0.0', $port = '9443') {
        $this->ip = $ip;
        $this->port = $port;

        register_shutdown_function(array($this, 'handleFatal'));
    }

    function setOption($key, $value = null){
        if(is_array($key)){
            $this->setting = array_merge($this->setting, $key);
        } else {
            $this->setting[$key] = $value;
        }
    }

    /**
     * @return HttpParser
     */
    public function getHttpParser() {
        if(!isset($this->httpParser)){
            $this->httpParser = new HttpParser();
        }
        return $this->httpParser;
    }

    /**
     *
     */
    function run(){
        if(!isset($this->sslKeyPath)){
            $this->sslKeyPath = __DIR__ . "/ssl";
        }

        if(isset($this->documentRoot)){
            $this->getHttpParser()->setDocumentRoot($this->documentRoot);
        }

        if(!isset($this->setting['ssl_cert_file'])){
            $this->setting['ssl_cert_file'] = $this->sslKeyPath . '/server.crt';
            $this->setting['ssl_key_file'] = $this->sslKeyPath . '/server_nopwd.key';
        }

        $http = new swoole_http_server(
            $this->ip,
            $this->port,
	        SWOOLE_PROCESS,
            SWOOLE_SOCK_TCP | SWOOLE_SSL
            );
        $http->set($this->setting);
        $this->pid($http);

        $dispatcher = $this;
        $http->on('request', function ($request, $response) use($dispatcher) {
            $dispatcher->execute($request, $response);
        });

        $http->start();
    }

    /**
     * @param swoole_http_server $server
     */
    protected function pid($server){
        if (!isset($this->setting['pid_file'])){
            return;
        }

        $pidFile = $this->setting['pid_file'];
        $server->on('Start', function($serv) use ($pidFile) {
            $this->log("Server start, pid {$serv->master_pid}");
            file_put_contents($pidFile, $serv->master_pid);
        });
        $server->on('ManagerStop', function($serv) use ($pidFile) {
            $this->log("Server stop");
            unlink($pidFile);
        });
    }


    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     * @param HttpParser $httpParser
     */
    function execute($request, $response, $httpParser = null){
        if(!$httpParser) {
            $httpParser = $this->getHttpParser();
        }

        if(!$httpParser->authorization($request, $response, "authorization")){
            // admin wg123123
            return;
        }

        $path = $request->server['request_uri'];
        if(strpos($path, $this->cgiPathPrefix) === 0){
            $action = substr($path, strlen($this->cgiPathPrefix));
            $this->log($action, 'debug');
            $this->processDynamic($request, $response, $action);
        } else {
            // client
            if (!$path || $path == '/') {
                $path = $path . $this->defaultIndex;
            }
            $this->log($path, 'debug');
            $httpParser->executeStatic($request, $response, $path);
        }
    }

    /**
     * @param string $action
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     */
    protected function processDynamic($request, $response, $action){
        if(!preg_match('/^[a-zA-Z_\/]+$/', $action)){
            $response->status(500);
            $response->end("Invalid cgi request, bad char");
            return;
        }

        try{
            $actionClass = $this->loadActionClass($action);
            if(!$actionClass){
                $response->status(500);
                $response->end("Invalid cgi request, action not found");
                return;
            }

            $actionClass->execute($request, $response);
        } catch(Exception $e){
            $response->status(500);
            $response->end($e->getCode() . " " . $e->getMessage());
        }
    }

    /**
     * @param $action
     * @return TraitAction
     */
    protected function loadActionClass($action){
        $actions = explode("/", $action);
        $ctrl = array_shift($actions);

        $class =  $this->actionNs  . ucfirst($ctrl);
        $this->log($class, 'debug');

        if(!class_exists($class)){
            return null;
        }

        /** @var TraitAction $instance */
        $instance = new $class();
        if($actions){
            $method = array_shift($actions);
            $this->log($method, 'debug');
            $instance->setAction($method);
        }

        return $instance;
    }

    /**
     * catch error
     */
    function handleFatal(){
        $error = error_get_last();
        if (!isset($error['type'])){
            return;
        }

        if(!in_array($error['type'], [
            E_ERROR,
            E_PARSE,
            E_DEPRECATED,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
        ])){
            return;
        }

        $message = $error['message'];
        $file = $error['file'];
        $line = $error['line'];
        $log = "$message ($file:$line)\nStack trace:\n";
        $trace = debug_backtrace();
        foreach ($trace as $i => $t)  {
            if (!isset($t['file'])) {
                $t['file'] = 'unknown';
            }
            if (!isset($t['line'])) {
                $t['line'] = 0;
            }
            if (!isset($t['function'])) {
                $t['function'] = 'unknown';
            }
            $log .= "#$i {$t['file']}({$t['line']}): ";
            if (isset($t['object']) && is_object($t['object'])){
                $log .= get_class($t['object']) . '->';
            }
            $log .= "{$t['function']}()\n";
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
        }
        //error_log($log);
        $this->log($log);
    }
}

/**
 * @example
 *
<?php
$vendorPath = realpath(__DIR__ . "/../../vendor/");
define('HOME_APP_ROOT', dirname($vendorPath));

require($vendorPath . "/classloader.php");
$loader = new \Composer\Autoload\ClassLoader();
$loader->add("j", $vendorPath);
$loader->add("server", HOME_APP_ROOT);
$loader->register();

$server = new j\network\https\Server();
$server->documentRoot = __DIR__ . "/client";
$server->actionNs = '\\server\\manager\cgi\\';
$server->setLogger(new j\log\Log());
$server->setOption('pid_file', __DIR__ . "/pid");
$server->setOption('daemonize', 1);

$server->run();
 *
 */