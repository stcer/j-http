<?php

namespace j\network\message\command;

use j\network\message\Customer as Model;
use j\log\Log;
use Exception;

/**
 * Class Customer
 * @package j\network\message\command
 */
class Customer extends Base {
    function listAction(){
        $this->responseJson(Model::toArray());
    }

    function delAction(){
        $id = $this->getVar('id');
        Model::deleteCustomer($id);
        $this->responseJson([
            'code' => 200,
            'message' => "delete success"
        ]);
    }

    function saveAction(){
        try{
            $c = [
                'events' => $this->getVar('events'),
                'url' => $this->getVar('url'),
                'tryTimes' => $this->getVar('tryTimes'),
                'id' => $this->getVar('id'),
                'enable' => $this->getVar('enable', 1),
                ];
            Log::add($c);
            Model::saveCustomer($c);
            $this->responseJson([
                'code' => 200,
                'message' => "save success"
                ]);
        } catch (Exception $e){
            $this->responseJson([
                'code' => 500,
                'message' => $e->getMessage()
                ]);
        }
    }
}