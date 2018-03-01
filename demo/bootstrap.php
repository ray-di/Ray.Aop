<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

/** @var $loader \Composer\Autoload\ClassLoader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->addPsr4('Ray\Aop\Demo\\', __DIR__ . '/src/');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

// tmp dir
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
