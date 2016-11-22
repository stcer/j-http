<?php


namespace j\network\message;

use Exception;
use Yar_client;

/**
 * Class Sender
 * @package j\network\message
 */
class Sender {
    public static function sendTest($data, $customer) {
        var_dump($data);
        var_dump($customer);
        echo "-----\n";
        return rand(0, 10);
    }

    /**
     * @param $data
     * @param $customer
     * @return bool
     */
    public static function send($data, $customer) {
        try{
            $client = new Yar_client($customer['url']);
            $rs = $client->run($data);
            return $rs == 'ok';
        } catch(Exception $e) {
            return false;
        }
    }
}