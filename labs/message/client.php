<?php

/**
 * Class MessageWorker
 */
class MessageWorker {
    /**
     * @param $data
     * @return string
     */
    function run($data){
        $msg = date("y-m-d H:i:s") . "\n";
        $msg .= var_export($data, true);
        file_put_contents(__DIR__ . "/message.log", $msg , FILE_APPEND);
        return 'ok';
    }

    function test(){
        return 'ok';
    }
}

$server = new \Yar_Server(new MessageWorker());
$server->handle();