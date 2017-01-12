<?php

namespace j\network\http;

use swoole_http_request;
use swoole_http_response;

/**
 * Class ParserStatic
 * @package j\network\http
 */
class ParserStatic {
    /**
     * @var string
     */
    protected $documentRoot;

    public $expireTime = 86400;

    /**
     * @var string
     * admin wg123123
     */
    public $authorKey = 'YWRtaW46d2cxMjMxMjM=';

    /**
     * @var static
     */
    private static $instance;

    /**
     * @return ParserStatic
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected $miniTypes = [
        'js' => 'application/x-javascript',
        'xml' => 'text/xml',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'jpeg' => 'image/jpeg',
        'pjpg' => 'image/pjpg',
        'jpg' => 'image/jpeg',
        'tif' => 'image/tiff',
        'htm' => 'text/html',
        'css' => 'text/css',
        'html' => 'text/html',
        'txt' => 'text/plain',
        'gz' => 'application/x-gzip',
        'tgz' => 'application/x-gzip',
        'tar' => 'application/x-tar',
        'zip' => 'application/zip',
        'hqx' => 'application/mac-binhex40',
        'doc' => 'application/msword',
        'pdf' => 'application/pdf',
        'ps' => 'application/postcript',
        'rtf' => 'application/rtf',
        'dvi' => 'application/x-dvi',
        'latex' => 'application/x-latex',
        'swf' => 'application/x-shockwave-flash',
        'tex' => 'application/x-tex',
        'mid' => 'audio/midi',
        'au' => 'audio/basic',
        'mp3' => 'audio/mpeg',
        'ram' => 'audio/x-pn-realaudio',
        'ra' => 'audio/x-realaudio',
        'rm' => 'audio/x-pn-realaudio',
        'wav' => 'audio/x-wav',
        'wma' => 'audio/x-ms-media',
        'wmv' => 'video/x-ms-media',
        'mpg' => 'video/mpeg',
        'mpga' => 'video/mpeg',
        'wrl' => 'model/vrml',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo'
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

    protected $pathAlias = [];

    /**
     * @param $alias
     * @param $path
     * @return $this
     */
    public function setPathAlias($alias, $path){
        $this->pathAlias[$alias] = $path;
        return $this;
    }

    /**
     * @param swoole_http_request $request
     * @param swoole_http_response $response
     * @param $documentRoot
     * @param $path
     */
    public function execute($request, $response, $path, $documentRoot = null){
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if(!$this->chkMiniType($ext)){
            $response->status(404);
            $response->end("Invalid request");
            return;
        }

        if(!$documentRoot){
            $documentRoot = $this->pathAlias
                ? $this->getDocRootPath($path)
                : $this->documentRoot;
        }

        $realPath = realpath($documentRoot . $path);
        if(!$realPath || !is_file($realPath)
           // || 0 !== strpos($realPath, $documentRoot)
        ){
            $response->status(404);
            $response->end("Request file not found");
            return;
        }

        $fileStat = stat($realPath);
        if (isset($request->header['if-modified-since'])){
            $lastModifiedSince = strtotime($request->header['if-modified-since']);
            if ($lastModifiedSince && $fileStat['mtime'] <= $lastModifiedSince){
                $response->status(304);
                $response->end();
                return;
            }
        }

        $response->header('Cache-Control', "max-age={$this->expireTime},must-revalidate");
        $response->header('Last-Modified', date('D, d-M-Y H:i:s T', $fileStat['mtime']));
        $response->header('Content-type', $this->miniTypes[$ext]);
        $response->sendfile($realPath);
    }

    protected function getDocRootPath($path){
        foreach($this->pathAlias as $prefix => $docRootPath){
            if(strpos($path, $prefix) === 0){
                return $docRootPath;
            }
        }

        return $this->documentRoot;
    }
}