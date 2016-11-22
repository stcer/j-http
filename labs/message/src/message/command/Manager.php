<?php

namespace j\network\message\command;

use j\network\message\Queue;
use j\network\message\Info;

/**
 * Class Manager
 * @package j\network\message\command
 */
class Manager extends Base {
    function closeAction(){
        $this->server->getSwoole()->shutdown();
        $this->responseJson(['code' => 200, 'message' => "server go shutdown"]);
    }

    function reloadAction(){
        $this->server->getSwoole()->reload();
        $this->responseJson(['code' => 200, 'message' => "server go reload"]);
    }

    function statusAction(){
        $data = Info::status(7);
        $data['queue'] = Queue::status();
        $data['time'] = date('Y-m-d H:i:s');
        $this->responseJson(['code' => 200, 'rs' => $data]);
    }
}