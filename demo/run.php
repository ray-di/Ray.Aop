<?php

declare(strict_types=1);

passthru('php ' . __DIR__ . '/01-explicit-bind.php');
passthru('php ' . __DIR__ . '/02-matcher-bind.php');
passthru('php ' . __DIR__ . '/03-annotation-bind.php');
passthru('php ' . __DIR__ . '/04-my-matcher.php');
passthru('php ' . __DIR__ . '/cache-write.php');
passthru('php ' . __DIR__ . '/05-cache.php');
