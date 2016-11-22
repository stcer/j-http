<?php

namespace j\network\http;

use Closure;
use swoole_http_server;
use swoole_http_request;
use swoole_http_response;
use j\log\TraitLog;


/**
 * Class Server
 * @package j\network\http
 * 
 * @property string $documentRoot
 * @property string $defaultIndex
 * @property string $cgiPathPrefix
 * @property string $actionNs
 * @property ParserDynamic|Closure $dynamicParser
 * @property ParserStatic $staticParser
 * @property bool $basicAuth
 * 
 */
class Server {
    use TraitLog;
	use TaskTrait;

    /**
     * @var string
     */
    public $ip;
    public $port;

    /**
     * @var array
     */
    protected $setting = [
        'worker_num' => 2,
        'dispatch_mode' => 1,
        ];

    /**
     * @var swoole_http_server
     */
    protected $server;

    /**
     * @var bool
     */
    protected $isSSL = false;
    protected $sslKeyPath;

    /**
     * @var callable|Protocol
     */
    public $parser;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param string $ip
     * @param int $port
     * @param [] $sslConfig
     */
    function __construct($ip = '0.0.0.0', $port = 8081, $sslConfig = []) {
        $this->ip = $ip;
        $this->port = $port;
        if($sslConfig){
            $this->setSSL($sslConfig);
        }

        $this->init();
        register_shutdown_function(array($this, 'handleFatal'));
        $this->server = $this->createServer();
    }

    protected function init() {
    }

    /**
     * @return swoole_http_server
     */
    function getSwoole(){
        return $this->server;
    }

    /**
     * @return swoole_http_server
     */
    protected function createServer() {
        return new swoole_http_server(
            $this->ip,
            $this->port,
            SWOOLE_PROCESS,
            $this->isSSL ? (SWOOLE_SOCK_TCP | SWOOLE_SSL) : SWOOLE_SOCK_TCP
        );
    }

    /**
     * @param true|string|array $conf
     */
    protected function setSSL($conf){
        if($conf === true || is_string($conf)){
            if(is_string($conf)){
                $this->sslKeyPath = $conf;
            } elseif(!isset($this->sslKeyPath)) {
                $this->sslKeyPath = __DIR__ . "/ssl";
            }

            if(!isset($this->setting['ssl_cert_file'])){
                $this->setting['ssl_cert_file'] = $this->sslKeyPath . '/server.crt';
                $this->setting['ssl_key_file'] = $this->sslKeyPath . '/server_nopwd.key';
            }
        } elseif(is_array($conf)) {
            if(isset($conf['sslKeyPath'])){
	            $this->sslKeyPath = $conf['sslKeyPath'];
            }

            foreach(['ssl_cert_file', 'ssl_key_file'] as  $key){
                $this->setting[$key] = $conf[$key];
            }
        }

        if($conf){
            $this->isSSL = true;
        }
    }

    /**
     * @param $key
     * @param null $value
     */
    function setOption($key, $value = null){
        if(is_array($key)){
            $this->setting = array_merge($this->setting, $key);
        } else {
            $this->setting[$key] = $value;
        }
    }

    function daemonize() {
        $this->setOption('daemonize', 1);
    }

    /**
     * @param swoole_http_server $http
     */
    protected function onServerCreate($http){
    }

    /**
     * server start()
     */
    function run(){
        $httpServer = $this->server;

        $this->onServerCreate($httpServer);

        // set options
        $httpServer->set($this->setting);

        // init server event
        $this->bindEvent($httpServer);

        // start server
        $this->log("start server on {$this->ip}:{$this->port}", "info");
        if(isset($this->onStartBefore)){
            call_user_func($this->onStartBefore, $this);
        }

        $httpServer->start();
    }

    /**
     * @var callable
     */
    protected $onStartBefore;


    /**
     * @param Closure $onStart
     */
    public function setOnStart(Closure $onStart) {
        $this->onStartBefore = $onStart;
    }

    /**
     * @param swoole_http_server $server
     */
    protected function bindEvent($server){
        $binds = [
            'onServerStart' => 'ManagerStart',
            'onServerStop' => 'ManagerStop',

            'onWorkerStart' => 'WorkerStart',
            'onWorkerStop' => 'WorkerStop',

            'onConnect' => 'Connect',
            'onReceive' => 'Receive',
            'onClose' => 'Close',

            'onTimer' => 'Timer',
            'onTask' => 'Task',
            'onFinish' => 'Finish',
            'onRequest' => 'request',
        ];

        foreach($binds as $method => $evt){
            if(method_exists($this, $method)) {
                $server->on($evt, array($this, $method));
            }
        }
    }

    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     */
    public function onRequest($request, $response){
        $this->response = new Response($response);
        $this->getParser()->onRequest($request, $response);
    }

    /**
     * @return callable|Protocol
     */
    public function getParser(){
        if(!isset($this->parser)){
            $this->parser = $parser = new Protocol($this);
            foreach([
                'documentRoot',
                'cgiPathPrefix',
                'basicAuth',
                'dynamicParser',
                'staticParser',
                'actionNs',
                'defaultIndex',
            ] as $key){
                if(isset($this->$key)){
                    $parser->$key = $this->$key;
                }
            }
            if(isset($this->logger)){
                $parser->setLogger($this->logger);
            }
        }
        return $this->parser;
    }
    

    function onServerStart($serv){
        $this->log("Server start, pid {$serv->master_pid}");
        if(!isset($this->setting['pid_file'])){
            return;
        }

        file_put_contents($this->setting['pid_file'], $serv->master_pid);
    }

    function onServerStop(){
        $this->log("Server stop");
        if(!isset($this->setting['pid_file'])){
            return;
        }
        unlink($this->setting['pid_file']);
    }

    /**
     * 任务管理器
     * @return TaskManager
     */
    public function getTaskManager(){
        if(!isset($this->taskManager)){
            if(!isset($this->setting['task_worker_num'])){
                $this->setting['task_worker_num'] = 2;
            }
            $this->log('create task manager');
            $this->taskManager = new TaskManager($this->server);
        }
        return $this->taskManager;
    }

    /**
     * catch error
     */
    function handleFatal(){
        if($this->response && !$this->response->isSend()){
            $this->response->setHttpStatus(500);
            $this->response->end("Server fatal error");
        }

        if($log = Debug::traceError()) {
            $this->log($log, "error");
        }
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