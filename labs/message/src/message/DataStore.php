<?php


namespace j\network\message;

use Redis;
use MongoClient, MongoDB;
use Exception;

ini_set('default_socket_timeout', -1);

/**
 * Class DataStore
 * @package j\network\message
 */
class DataStore {
    public static $redis = [
        //'host' => '127.0.0.1',
        'host' => '192.168.0.234',
        'port' => 6379,
    ];

    private static $redisInstance;

    /**
     * @param bool|false $new
     * @return Redis
     */
    public static function getRedis($new = false) {
        if ($new || !isset(self::$redisInstance)) {
            $redis = new Redis();
            $redis->connect(self::$redis['host'], self::$redis['port'], 0);
            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            if($new){
                return $redis;
            }
            self::$redisInstance = $redis;
        }
        return self::$redisInstance;
    }

    public static function releaseRedis(){
        self::$redisInstance = null;
    }

//    public static $mongo = [
//        'server' => 'mongodb://127.0.0.1:27017',
//        'db' => 'j_message',
//        'tableMsg' => 'message',
//        ];

    /**
     * @param null $db
     * @return MongoDB
     */
//    static function getDb($db = null) {
//        static $instance = [];
//        $db = $db ?: self::$mongo['db'];
//        if(!isset($instance[$db])){
//            $m = self::getMongoClient(self::$mongo['server']);
//            $instance[$db] = $m->selectDB($db);
//        }
//        return $instance[$db];
//    }
//
//    static function getMsgTable(){
//        return self::getDb()->selectCollection(self::$mongo['tableMsg']);
//    }

    /**
     * @param string $server
     * @param array $options
     * @param int $retry
     * @return MongoClient
     * @throws Exception
     */
//    private static function getMongoClient($server = "", $options = array(), $retry = 3) {
//        try {
//            return new MongoClient($server, $options);
//        } catch(Exception $e) {
//        }
//
//        if ($retry > 0) {
//            return self::getMongoClient($server, $options, --$retry);
//        }
//
//        throw new Exception("I've tried several times getting MongoClient..");
//    }
}