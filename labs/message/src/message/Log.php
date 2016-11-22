<?php

namespace j\network\message;

/**
 * Class Log
 * @package j\network\message
 * todo 将历史数据移动历史记录
 */
class Log {

    public $listSuccess = 'j_msg_list_success';
    public $listMessage = 'j_msg_list_message';
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * msg LogInterface
     * @param $event
     * @param $message
     * @param $n
     * @return int
     */
    function addMessage($event, $message, $n = 0){
        return DataStore::getRedis()->lPush($this->listMessage, [
            'evt' => $event,
            'msg' => $message,
            'time' => time(),
            'totalTask' => $n,
            'success' => [],
            'errors' => [],
        ]);
    }

    /**
     * success log
     * @param $data
     */
    public function addSuccess($data){
    }
}