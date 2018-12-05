<?php

declare(strict_types=1);

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/* @var \Composer\Autoload\ClassLoader $loader */
$loader->addPsr4('Ray\Aop\Demo\\', __DIR__ . '/src/');

// tmp dir
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
