<?php
namespace Ray\Aop\Demo;

require __DIR__ . '/bootstrap.php';

use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;

$cache = __FILE__ . '.cache';
if (file_exists($cache)) {
    unlink($cache);
}

$start = microtime(true);
no_cache: {
    $pointcut = new Pointcut(
        (new Matcher)->any(),
        (new Matcher)->annotatedWith(WeekendBlock::class),
        [new WeekendBlocker]
    );
    $compiler = new Compiler($_ENV['TMP_DIR']);
    $bind = (new Bind)->bind(AnnotationRealBillingService::class, [$pointcut]);
    $billingService1 = $compiler->newInstance(AnnotationRealBillingService::class, [], $bind);
}
$time1 = microtime(true) - $start;

file_put_contents($cache, serialize($bind));

$start = microtime(true);
cache_enable: {
    $bind = unserialize(file_get_contents($cache));
    $billingService2 = $compiler->newInstance(AnnotationRealBillingService::class, [], $bind);
}
$time2 = microtime(true) - $start;

$works = $billingService1 instanceof AnnotationRealBillingService && $billingService2 instanceof AnnotationRealBillingService;
echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
echo 'x' . round($time1 / $time2) . ' times faster.' . PHP_EOL;
unlink($cache);
