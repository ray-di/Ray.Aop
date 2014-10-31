<?php

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Aop\Demo\\', __DIR__ . '/src/');

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
