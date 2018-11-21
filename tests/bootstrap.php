<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';
$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
array_map('unlink', glob("{$_ENV['TMP_DIR']}/*.php"));
