<?php

namespace j\network\https;

use swoole_http_request;
use swoole_http_response;

class HttpParser{
    /**
     * @var string
     */
    protected $documentRoot;

    /**
     * @var string
     * admin wg123123
     */
    public $authorKey = 'YWRtaW46d2cxMjMxMjM=';

    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected $miniTypes = [
        'js' => 'application/x-javascript',
        'jpg' => 'application/x-javascript',
        'git' => 'image/gif',
        'css' => 'text/css',
        'html' => 'text/html',
        'htm' => 'text/html',
        'xml' => 'text/xml',
        ];

    protected function chkMiniType($ext){
        if(!$ext || !isset($this->miniTypes[$ext])){
            return false;
        }
        return true;
    }

    /**
     * @param mixed $documentRoot
     */
    public function setDocumentRoot($documentRoot) {
        $this->documentRoot = $documentRoot;
    }


    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     * @param $title
     * @return bool
     */
    public function authorization($request, $response, $title = 'XXX'){
        // admin wg123123
        if(!isset($request->header['authorization'])
            || $request->header['authorization'] != 'Basic ' . $this->authorKey
        ) {
            $response->header('WWW-authenticate', 'basic realm="' . $title. '"');
            $response->status(401);
            $response->end("Login Instructions");
            return false;
        }

        return true;
    }

    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     * @param $documentRoot
     * @param $path
     */
    public function executeStatic($request, $response, $path, $documentRoot = null){
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if(!$this->chkMiniType($ext)){
            $response->status(404);
            $response->end("Invalid request");
            return;
        }

        if(!$documentRoot){
            $documentRoot = $this->documentRoot;
        }

        $realPath = realpath($documentRoot . $path);
        if(!$realPath
            || !is_file($realPath)
            || 0 !== strpos($realPath, $documentRoot)
        ){
            $response->status(404);
            $response->end("Request file not found");
            return;
        }

        $response->header('Content-type', $this->miniTypes[$ext]);
        $response->end(file_get_contents($realPath));
    }
}