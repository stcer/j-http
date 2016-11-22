<?php

namespace j\network\message;

use Exception;
use j\network\http\Server as Base;
use swoole_server;
use j\network\message\command\Message;
use swoole_process;

/**
 * Class Server
 * @package j\network\message
 */
class Server extends Base{
    protected $setting = array(
        'dispatch_mode' => 3,
        'max_request' => 10000,
        'worker_num' => 2,       //worker process num
        'task_worker_num' => 10,
        'backlog' => 128,        //listen backlog
        'open_tcp_keepalive' => 1,
//        'heartbeat_check_interval' => 5,
//        'heartbeat_idle_time' => 10,
        'http_parse_post' => false,
        );

    public $sendProcessNumbers = 5;

    /**
     *
     */
    protected function init() {
        parent::init();
        $this->documentRoot = __DIR__ . "/client/";
        $this->actionNs = __NAMESPACE__ . "\\command\\";
        Info::reset();
    }

    /**
     * @param \swoole_http_server $server
     */
    protected function onServerCreate($server) {
        $that = $this;
        for($i = 0; $i < $this->sendProcessNumbers; $i++){
            $server->addProcess(new swoole_process(function($process) use($that) {
                $redis = DataStore::getRedis();
                $key = Queue::$listSend;
                while($data = $redis->blPop($key, 0)){
                    $that->processMessage($data[1]);
                }
            }));
        }
    }

    /**
     * @var callback
     */
    public $processor;
    function getProcess() {
        if(!isset($this->processor)){
            $this->processor = array(__NAMESPACE__ . '\\Sender', 'send');
        }

        return $this->processor;
    }

    function processMessage($data){
        if(!$data || !is_array($data)){
            return false;
        }

        try{
            $customer = Customer::getCustomer($data['customerId']);
            if(!$customer || isset($customer['enable']) && !$customer['enable']){
                return false;
            }

            $rs = call_user_func($this->getProcess(), $data, $customer, $this);

            if($rs){
                // add send success log
                Info::incrSuccess($data);
            } else {
                // add send error log
                Queue::addError($data, $customer);
                Info::incrError($data);
            }
            return $rs;
        } catch(\Exception $e){
            // todo 记录丢失的发送数据 $data
            $this->log($e->getMessage());
        }
    }

    /**
     * @param $serv
     * @param $task_id
     * @param $from_id
     * @param $queueKey
     * @return array
     */
    function onTask($serv, $task_id, $from_id, $queueKey) {
        $data = Queue::pop($queueKey);
        $returnValue = Queue::has($queueKey) ? $queueKey : null;
        $this->processMessage($data);
        return $returnValue;
    }

    /**
     * @param swoole_server $serv
     * @param $task_id
     * @param $data
     */
    function onFinish($serv, $task_id, $data) {
        if($data && is_string($data)){
            // 队列中还有数据, 继续发送
            $serv->task($data);
        }
    }

    /**
     * @param swoole_server $serv
     */
    public function onWorkerStart($serv) {
        if($serv->taskworker || $serv->worker_id != 0) {
            return;
        }

        // 发送错误的队列
        $that = $this;
        $timeUnit = 60; // Minutes
        $times = [1, 5, 30, 180, 1440];
        foreach($times as $n => $time) {
            $serv->tick($time * $timeUnit * 1000, function($id) use($that, $n){
                // 发送错误队列
                $queueKey = Queue::getErrorKey($n + 1);
                $that->server->task($queueKey);
            });
        }
    }
}