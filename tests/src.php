<?php
require_once dirname(__DIR__) . '/vendor/Doctrine.Common/lib/Doctrine/Common/ClassLoader.php';
$commonLoader = new \Doctrine\Common\ClassLoader('Doctrine\Common', dirname(__DIR__) . '/vendor/Doctrine.Common/lib');
$commonLoader->register();

require __DIR__ . '/MockMethod.php';
require __DIR__ . '/MockMethodInterceptor.php';
require __DIR__ . '/Mock/AnnotateClass.php';
require __DIR__ . '/interceptors/DoubleInterceptor.php';
require __DIR__ . '/interceptors/voidInterceptor.php';
// annotation
require __DIR__ . '/Annotation/Marker.php';
require __DIR__ . '/Annotation/Resource.php';