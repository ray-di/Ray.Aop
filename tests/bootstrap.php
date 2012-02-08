<?php
// bootstrap for test
require dirname(__DIR__) . '/vendor/Doctrine.Common/lib/Doctrine/Common/ClassLoader.php';
$commonLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', dirname(__DIR__) . '/vendor/Doctrine.Common/lib');
$commonLoader->register();

require dirname(__DIR__) . '/src.php';
require __DIR__ . '/src.php';
