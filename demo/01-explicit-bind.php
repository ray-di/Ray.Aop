<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

require __DIR__ . '/bootstrap.php';

use Ray\Aop\Bind;
use Ray\Aop\Compiler;

$compiler = new Compiler(__DIR__ . '/tmp');
$bind = (new Bind)->bindInterceptors(
    'chargeOrder',        // method name
    [new WeekendBlocker]  // interceptors
);
$billingService = $compiler->newInstance(RealBillingService::class, [], $bind);

try {
    echo $billingService->chargeOrder();
    exit(0);
} catch (\RuntimeException $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}
