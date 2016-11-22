<?php

namespace cgi;

use j\network\http\AbstractAction;
use j\network\http\Response;
use j\mongo\Table;
use MongoCursorException, Exception, MongoConnectionException;

class Mongo extends AbstractAction {
    function indexAction(){
        try{
            $table = new Table("user");
            $list = $table->find();
            $info = $list->getNext();
        } catch (MongoCursorException $e) {
            $info = [
                'query' => get_class($e),
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        } catch (MongoConnectionException $e) {
            $info = [
                'conn' => get_class($e),
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        } catch (Exception $e) {
            $info = [
                'class' => get_class($e),
                'code' => $e->getCode(),
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        $this->response(var_export($info, true));
    }
}