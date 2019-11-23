<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use Ray\Aop\Compiler;

require __DIR__ . '/bootstrap.php';

$cache = '.cache';
if (! file_exists(__DIR__ . '/.cache')) {
    throw new \RuntimeException('Run cache-write.php first');
}

$max = 1;
$start = microtime(true);
$compiler = new Compiler($_ENV['TMP_DIR']);
cache_reads:
    for ($i = 0; $i < $max; $i++) {
        [$bind, $time1] = unserialize(file_get_contents($cache));
        var_dump($bind);
        $billingService2 = $compiler->newInstance(AnnotationRealBillingService::class, [], $bind);
    }

$time2 = microtime(true) - $start;

$works = $billingService2 instanceof AnnotationRealBillingService;
echo($works ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
printf("x%s times faster\n", number_format($time1 / $time2, 2));
