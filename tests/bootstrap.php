<?php

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$testDirs = [
    __DIR__,
    __DIR__ . '/Fake',
    __DIR__ . '/Fake/Annotation',
    __DIR__ . '/Fake/Interceptor'
];
$loader->addPsr4('Ray\Aop\\', $testDirs);
$loader->add('', 'template');

$clear = function ($dir) {
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($dir),
        \RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        /* @var $file \SplFileInfo */
        if ($file->getFilename()[0] !== '.') {
            @unlink($file);
        }
    }
};
$clear(__DIR__ . '/tmp');
