<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use Doctrine\Common\Annotations\AnnotationRegistry;

/** @var $loader \Composer\Autoload\ClassLoader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->addPsr4('Ray\Aop\Demo\\', __DIR__ . '/src/');
AnnotationRegistry::registerLoader([$loader, 'loadClass']);

// tmp dir
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
