<?php

// vendor
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Aop\\', [__DIR__]);
$loader->addPsr4('Ray\Aop\\', [__DIR__ . '/Annotation']);
$loader->addPsr4('Ray\Aop\\', [__DIR__ . '/Fake']);
$loader->addPsr4('Ray\Aop\\', [__DIR__ . '/Interceptor']);
$loader->add('', 'template');

// remove compiled files
$path = __DIR__ . '/tmp';
$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
foreach ($iterator as $file) {
    /* @var $file \SplFileInfo */
    if ($file->getFilename()[0] !== '.') {
        @unlink($file);
    }
}
