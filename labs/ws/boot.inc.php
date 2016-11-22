<?php

define("DEV_MODE", true);
define('PATH_ROOT', __DIR__ . '/');
define('PATH_CONFIG', PATH_ROOT . 'config/');

date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 0);

// class auto loader
$vendorPath = realpath(__DIR__ . "/../../vendor/");
$loader = include($vendorPath . "/autoload.php");
$loader->addPsr4('ws\\', __DIR__ . "/src/");