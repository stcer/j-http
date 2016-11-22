<?php

namespace ws;

/**
 * Class Store
 * @package ws
 */
class Store {
	/**
	 * @var Store
	 */
	private static $instance;

	/**
	 * @return Store
	 */
	public static function getInstance(){
		if(!isset(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public $path;

	/**
	 * Store constructor.
	 * @param string $path
	 */
	public function __construct($path = ''){
		if(!$path)
			$path = __DIR__ . '/../var/link';

		$this->path = $path;
	}

	function addOnline($fd, $info = []){
		$info['loginTime'] = date('Y-m-d H:i:s');
		$this->saveUserInfo($fd, $info);
	}

	function isOnline($fd){
		$file = $this->genFilePath($fd);
		return file_exists($file);
	}

	function getUserInfo($fd, $chk = true){
		if($chk && !$this->isOnline($fd)){
			return [];
		}

		$file = $this->genFilePath($fd);
		$content = file_get_contents($file);
		return json_decode($content, true);
	}

	function saveUserInfo($fd, $info){
		$file = $this->genFilePath($fd);
		$info['fd'] = (int)$fd;
		return file_put_contents($file, json_encode($info));
	}

	function delOnline($fd){
		$file = $this->genFilePath($fd);
		if(!file_exists($file)){
			return false;
		} else {
			return unlink($file);
		}
	}

	function clearOnline() {
		$this->clearFiles($this->path);
	}

	public function allOnline($info = false, $excludeFd = 0){
		$files = scandir($this->path);
		$tmp = [];
		foreach($files as $f){
			if($f == '.' || $f == '..'){
				continue;
			}

			if($excludeFd && $excludeFd == $f){
				continue;
			}

			if(!preg_match('/^\d+$/', $f)){
				continue;
			}

			if($info){
				$tmp[$f] = $this->getUserInfo($f, false);
			} else {
				$tmp[] = $f;
			}
		}
		return $tmp;
	}

	private function genFilePath($fd){
		return $this->path . '/' . (int)$fd;
	}

	/**
	 * @param $dir
	 */
	private static function clearFiles($dir){
		if (!is_dir($dir)) {
			return;
		}

		if (!($handle = opendir($dir))) {
			return;
		}

		while (($file = readdir($handle)) !== false) {
			if ($file === '.' || $file === '..' || !preg_match('/^\d+$/', $file)) {
				continue;
			}
			$path = $dir . DIRECTORY_SEPARATOR . $file;
			if (!is_dir($path)) {
				unlink($path);
			}
		}
		closedir($handle);
	}
}