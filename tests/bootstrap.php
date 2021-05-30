<?php

declare(strict_types=1);

namespace Ray\Aop;

use function dirname;
use function passthru;
use function sprintf;

require dirname(__DIR__) . '/vendor/autoload.php';
[$classDir, $cacheDir] = require __DIR__ . '/define.php';
deleteFiles($classDir);
deleteFiles($cacheDir);
passthru(sprintf('php %s/cache-write.php', __DIR__));
