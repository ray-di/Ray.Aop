<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;

require __DIR__ . '/bootstrap.php';

$pointcut = new Pointcut(
    (new Matcher)->any(),
    (new Matcher)->annotatedWith(WeekendBlock::class),
    [new WeekendBlocker]
);
$bind = (new Bind)->bind(AnnotationRealBillingService::class, [$pointcut]);
$compiler = new Compiler($_ENV['TMP_DIR']);
$billingService = $compiler->newInstance(AnnotationRealBillingService::class, [], $bind);

try {
    echo $billingService->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
