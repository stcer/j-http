<?php

namespace j\network\message;

/**
 * Class Queue
 * @package j\network\message
 */
class Queue {

    public static $listError = 'j_msg_list_error';
    public static $listSend = 'j_msg_list_send';
    private static $maxTimes = 5;

    /**
     * @param $data
     * @return int
     */
    public static function pushReady($data) {
        return DataStore::getRedis()->lPush(self::$listSend, $data);
    }

    public static function pop($key = null){
        if(!$key){
            $key = self::$listSend;
        }
        return DataStore::getRedis()->rPop($key);
    }

    public static function has($key = null){
        if(!$key){
            $key = self::$listSend;
        }
        return DataStore::getRedis()->lLen($key) > 0 ? 1 : 0;
    }

    /**
     * @return array
     */
    public static function status(){
        $redis = DataStore::getRedis();
        $data = [
            'ready' => $redis->lLen(self::$listSend),
            'error' => $redis->lLen(self::$listError),
            ];

        for($i = 0; $i < self::$maxTimes; $i++){
            $index = $i + 1;
            $key = self::$listError . $index;
            $data['error' . $index] = $redis->lLen($key);
        }

        return $data;
    }

    /**
     * error log
     * sendId to error set,
     * add error time and try times to send log
     *
     * @param $data
     * @param $customer
     */
    public static function addError($data, $customer){
        $redis = DataStore::getRedis();

        if(!isset($data['errorTimes'])){
            $data['errorTimes'] = 0;
        }

        $data['errorTimes']++;

        $times = $data['errorTimes'];
        $isTry = $customer['tryTimes'] > $times;

        if(!$isTry){
            $key = self::$listError;
        } else {
            if($times > self::$maxTimes){
                $times = self::$maxTimes;
            }
            $key = self::$listError . $times;
        }

        $redis->lPush($key, $data);
    }

    public static function getErrorKey($n){
        if(!$n){
            return self::$listError;
        }

        if($n > self::$maxTimes){
            $n = self::$maxTimes;
        }

        return self::$listError . $n;
    }
}