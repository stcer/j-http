<?php

namespace j\network\message\command;

use j\network\message\Queue;
use j\network\message\Log;
use j\network\message\Info;
use j\network\message\Customer as C;

/**
 * Class Message
 * @package j\network\message\command
 */
class Message extends Base {
    /**
     * todo
     * 如果redis连接失败，如何处理消息的问题
     */
    function pushAction(){
        $event = $this->getVar('broker');
        $msg = $this->getVar('msg');

        if(!$event || !$msg){
            $this->responseJson(['code' => 500, 'msg' => "Invalid event or message"]);
            return;
        }

        $n = 0;
        foreach(C::toArray() as  $c){
            if(!isset($c['events'])){
                continue;
            }

            if(!is_array($c['events'])){
                $c['events'] = array($c['events']);
            }

            if(!in_array($event, $c['events'])){
                if(!in_array('all', $c['events'])){
                    continue;
                }
            }

            $n++;
            $data = [
                'msg' => $msg,
                'event' => $event,
                'customerId' => $c['id'],
                ];

            // 加入发送队列
            Queue::pushReady($data);
        }

        $this->responseJson(['code' => 200]);

        // log msg;
        Info::incrMessage();
        Log::getInstance()->addMessage($event, $msg, $n = 0);
    }

    function statusAction(){
        $data = Info::status(6);
        $data['time'] = date('Y-m-d H:i:s');
        $this->responseJson(['code' => 200, 'data' => $data]);
    }
}