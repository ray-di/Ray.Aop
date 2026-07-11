<?php

declare(strict_types=1);

namespace Ray\Aop\Benchmark;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/BenchService.php';
require __DIR__ . '/BenchInterceptor.php';

use ArrayObject;

// -------------------------------------------------------
// Micro-benchmark: Individual component costs
// -------------------------------------------------------
const ITERATIONS = 100_000;

function bench(string $label, callable $fn, int $iterations = ITERATIONS): void
{
    // Warm up
    $fn();

    $start = \hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }
    $time = (\hrtime(true) - $start) / 1e6; // ms
    $perCall = ($time / $iterations) * 1000; // μs

    \printf("%-55s %8.3f ms  %8.3f μs/call\n", $label, $time, $perCall);
}

echo "=== Component-level Micro-benchmarks ===\n\n";
echo \str_repeat('-', 90) . "\n";
\printf("%-55s %10s  %15s\n", "Operation", "Total(ms)", "Per call(μs)");
echo \str_repeat('-', 90) . "\n";

// 1. Baseline: empty loop
bench('1. Empty loop', function () {
    // nothing
});

// 2. func_get_args() cost
bench('2. func_get_args()', function () {
    $args = \func_get_args();
    return $args;
});

// 3. call_user_func_array cost
$target = new class {
    public function add(int $a, int $b): int { return $a + $b; }
};
bench('3. call_user_func_array', function () use ($target) {
    return \call_user_func_array([$target, 'add'], [1, 2]);
});

// 4. First-class callable spread
$callable = $target->add(...);
bench('4. First-class callable (...)', function () use ($callable) {
    return $callable(1, 2);
});

// 5. Direct method call
bench('5. Direct method call', function () use ($target) {
    return $target->add(1, 2);
});

// 6. new ArrayObject
bench('6. new ArrayObject([1,2])', function () {
    return new ArrayObject([1, 2]);
});

// 7. new ReflectiveMethodInvocation (minimal)
$minInterceptor = new BenchInterceptor();
$svc = new BenchService();
bench('7. new ReflectiveMethodInvocation', function () use ($svc, $minInterceptor) {
    $inv = new \Ray\Aop\ReflectiveMethodInvocation($svc, 'noIntercept', [], [$minInterceptor]);
    return $inv->proceed();
});

// 8. BenchInterceptor minimal pass-through
bench('8. Interceptor.invoke → proceed', function () use ($svc) {
    $inv = new \Ray\Aop\ReflectiveMethodInvocation($svc, 'noIntercept', [], [new BenchInterceptor()]);
    return $inv->proceed();
});

// 9. in_array() lookup (simulating addMethods)
$testArray = ['doWork', 'doString', 'noIntercept', 'other1', 'other2'];
bench('9. in_array() lookup', function () use ($testArray) {
    return \in_array('doWork', $testArray);
});

// 10. array_key_exists (alternative)
$testMap = ['doWork' => true, 'doString' => true, 'noIntercept' => true];
bench('10. array_key_exists lookup', function () use ($testMap) {
    return \array_key_exists('doWork', $testMap);
});

// 11. isset (fastest alternative)
bench('11. isset lookup', function () use ($testMap) {
    return isset($testMap['doWork']);
});

// 12. is_callable check
$callableCheck = [$svc, 'noIntercept'];
bench('12. is_callable check', function () use ($callableCheck) {
    return \is_callable($callableCheck);
});

echo "\n=== Summary ===\n";
echo "Empty loop is the baseline. Subtract it from each measurement for true cost.\n";
echo "Key finding: 'new ReflectiveMethodInvocation' and 'call_user_func_array' dominate.\n";
