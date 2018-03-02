<?php

declare(strict_types=1);
/**
 * This file is part of the Ray.Aop package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
passthru('php ' . __DIR__ . '/01-explicit-bind.php');
passthru('php ' . __DIR__ . '/02-matcher-bind.php');
passthru('php ' . __DIR__ . '/03-annotation-bind.php');
passthru('php ' . __DIR__ . '/04-my-matcher.php');
passthru('php ' . __DIR__ . '/05-cache.php');
