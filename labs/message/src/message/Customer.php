<?php

namespace j\network\message;

use Yar_client;
use Exception;

/**
 * Class Customer
 * @package j\network\message
 */
class Customer {

    protected static $key = 'j_message_customers';

	/**
	 * @param $c
	 * @return mixed
	 * @throws Exception
	 */
    protected static function valid($c) {
        $events = $c['events'];
        if(!is_array($events)) {
            $events = explode(",", $events);
        }
        $events = array_map('trim', $events);
        $events = array_filter($events);
        $c['events'] = $events;

        if(!$events) {
            throw new \Exception("Invalid events");
        }

        $url = $c['url'];
        if(strpos($url, 'http:') !== 0) {
            throw new \Exception("Invalid url, eg: 'http://xxx.com/path'");
        }

        try{
            $client = new Yar_client($c['url']);
            $rs = $client->test();
            if($rs == 'ok'){
            } else {
                throw new \Exception("Invalid url, can not call test()");
            }
        } catch (Exception $e) {
            throw $e;
        }

        if(!$c["tryTimes"]) {
            $c["tryTimes"] = 0;
        }

        return $c;
    }

    /**
     * @param $c
     * @throws \Exception
     */
    public static function saveCustomer($c) {
        $c = self::valid($c);

        if(!$c['id']){
            $c['id'] = uniqid();
        }

        DataStore::getRedis()->hSet(self::$key, $c['id'], $c);
    }


    /**
     * @param string $id
     */
    public static function deleteCustomer($id) {
        DataStore::getRedis()->hDel(self::$key, $id);
    }

    public static function getCustomer($id) {
        return DataStore::getRedis()->hGet(self::$key, $id);
    }

    /**
     * @param bool|false $onlyId
     * @return array
     */
    public static function toArray($onlyId = false){
        if($onlyId){
            $data = DataStore::getRedis()->hKeys(self::$key);
        } else {
            $data = DataStore::getRedis()->hVals(self::$key);
        }

        if(!$data){
            return [];
        }

        return $data;
    }
}