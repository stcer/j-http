<?php

/**
 * 早期测试
 */
$vendorPath = realpath(__DIR__ . "/../../vendor/");

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = include($vendorPath . "/autoload.php");

$loader->addPsr4("j\\network\\https\\", __DIR__ . '/src/https');
$loader->add("cgi", __DIR__);
$loader->register();

$server = new j\network\https\Server();
$server->documentRoot = __DIR__ . "/www/";
$server->run();