<?php

namespace j\network\message;

/**
 * Class Info
 * @package j\network\message
 */
class Info {
    private static $key = 'j_msg_info';
    private static $keyEvent = 'j_msg_info_counter1';
    private static $keyCustomer = 'j_msg_info_counter2';
    private static $keyEventErr = 'j_msg_info_counter3';
    private static $keyCustomerErr = 'j_msg_info_counter4';

    static function incrSuccess($data){
        $redis = DataStore::getRedis();
        $redis->hIncrBy(self::$key, 'success', 1);
        $redis->hIncrBy(self::$keyCustomer, $data['customerId'], 1);
        $redis->hIncrBy(self::$keyEvent, $data['event'], 1);
    }

    static function incrError($data){
        $redis = DataStore::getRedis();
        $redis->hIncrBy(self::$key, 'error', 1);
        $redis->hIncrBy(self::$keyCustomerErr, $data['customerId'], 1);
        $redis->hIncrBy(self::$keyEventErr, $data['event'], 1);
    }

    static function incrSend(){
        DataStore::getRedis()->hIncrBy(self::$key, 'send', 1);
    }

    static function incrMessage(){
        DataStore::getRedis()->hIncrBy(self::$key, 'msg', 1);
    }

    static function reset(){
        $redis = DataStore::getRedis(true); // 必须为true
        $redis->del(self::$key);
        $redis->del(self::$keyEvent);
        $redis->del(self::$keyCustomer);
        $redis->del(self::$keyEventErr);
        $redis->del(self::$keyCustomerErr);
    }

    static function status($part = 1){
        $redis = DataStore::getRedis();
        $data = [];
        if($part & 1){
            $data['summary'] = $redis->hMGet(self::$key, ['msg', 'success', 'error']);
        }

        if($part & 2){
            $data['event_success'] = $redis->hGetAll(self::$keyEvent);
            $data['event_error'] = $redis->hGetAll(self::$keyEventErr);
        }

        if($part & 4){
            $data['customer_success'] = $redis->hGetAll(self::$keyCustomer);
            $data['customer_error'] = $redis->hGetAll(self::$keyCustomerErr);
        }

        return $data;
    }
}
