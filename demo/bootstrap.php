<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
assert($loader instanceof ClassLoader);
$loader->addPsr4('Ray\Aop\Demo\\', __DIR__ . '/src/');
