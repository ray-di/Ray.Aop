<?php
// bootstrap for test
require '/Users/akihito/git/Ray.Di/vendor/Ray.Aop/vendor/Doctrine.Common/lib/Doctrine/Common/Annotations/Reader.php';
require dirname(__DIR__) . '/vendor/Doctrine.Common/lib/Doctrine/Common/ClassLoader.php';
$commonLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', dirname(__DIR__) . '/vendor/Doctrine.Common/lib');
$commonLoader->register();

require dirname(__DIR__) . '/src.php';
require __DIR__ . '/src.php';