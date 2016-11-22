<?php

ini_set('default_socket_timeout', -1);

function getRedis() {
    static $redis;

    if(!isset($redis)){
        $redis = new Redis();
        $redis->pconnect('192.168.0.252', 6379);
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
    }

    return $redis;
}

$redis = getRedis();
while($data = $redis->blPop(['j_msg_list_send'], 0)){
    var_dump($data);
}