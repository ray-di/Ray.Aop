<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

// library
require dirname(__DIR__) . '/src.php';
// tests
require __DIR__ . '/src.php';
// vendor
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->add('Ray\Aop', [__DIR__]);
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
