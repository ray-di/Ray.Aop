<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

//This ensures that there will be no errors when traversing highly nested node trees.
ini_set('xdebug.max_nesting_level', 2000);

// vendor
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
/** @var $loader \Composer\Autoload\ClassLoader */
$loader->addPsr4('Ray\Aop\\', [__DIR__]);
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

require dirname(__DIR__) . '/src/Compiler/Template.php';

// remove compiled files
$path = __DIR__ . '/Weaved';
$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
foreach ($iterator as $file) {
    /* @var $file \SplFileInfo */
    if ($file->getFilename()[0] !== '.') {
        @unlink($file);
    }
}
