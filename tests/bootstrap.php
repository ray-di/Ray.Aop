<?php

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Aop\\', [__DIR__, __DIR__ . '/Fake']);
$loader->add('', 'template');
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
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
