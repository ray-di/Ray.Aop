<?php

declare(strict_types=1);

namespace Ray\Aop\Benchmark;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/BenchService.php';
require __DIR__ . '/BenchInterceptor.php';

use Ray\Aop\Aspect;
use Ray\Aop\Matcher;

// -------------------------------------------------------
// Clean generated files
// -------------------------------------------------------
\array_map(\unlink(...), \array_filter((array) \glob(__DIR__ . '/tmp/*.php')));

// -------------------------------------------------------
// Compile phase benchmark
// -------------------------------------------------------
$compileStart = \hrtime(true);

$aspect = new Aspect(__DIR__ . '/tmp');
$aspect->bind(
    (new Matcher())->any(),
    (new Matcher())->startsWith('do'),
    [new BenchInterceptor()],
);

$service = $aspect->newInstance(BenchService::class);

$compileTime = (\hrtime(true) - $compileStart) / 1e6; // ms

\printf("=== Compile Phase ===\n");
\printf("  Time: %.3f ms\n\n", $compileTime);

// -------------------------------------------------------
// Runtime hot-path benchmark (5K iterations)
// -------------------------------------------------------
$iterations = 5_000;

// Warm up (ensure OPcache is hot, etc.)
$service->doWork(1, 2);

// Benchmark intercepted call
$runtimeStart = \hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $service->doWork($i, $i + 1);
}
$interceptTime = (\hrtime(true) - $runtimeStart) / 1e6; // ms

\printf("=== Intercepted call (doWork) ===\n");
\printf("  Iterations: %d\n", $iterations);
\printf("  Total: %.3f ms\n", $interceptTime);
\printf("  Per call: %.3f μs\n\n", ($interceptTime / $iterations) * 1000);

// Benchmark non-intercepted call
$runtimeStart = \hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $service->noIntercept();
}
$nonInterceptTime = (\hrtime(true) - $runtimeStart) / 1e6;

\printf("=== Non-intercepted call (noIntercept) ===\n");
\printf("  Iterations: %d\n", $iterations);
\printf("  Total: %.3f ms\n", $nonInterceptTime);
\printf("  Per call: %.3f μs\n\n", ($nonInterceptTime / $iterations) * 1000);

// Benchmark string return method
$runtimeStart = \hrtime(true);
for ($i = 0; $i < $iterations; $i++) {
    $service->doString('hello');
}
$stringTime = (\hrtime(true) - $runtimeStart) / 1e6;

\printf("=== Intercepted call (doString) ===\n");
\printf("  Iterations: %d\n", $iterations);
\printf("  Total: %.3f ms\n", $stringTime);
\printf("  Per call: %.3f μs\n\n", ($stringTime / $iterations) * 1000);

// Overhead ratio
$overhead = ($interceptTime / $iterations) / ($nonInterceptTime / $iterations);
\printf("=== Overhead ===\n");
\printf("  Ratio (intercepted/non-intercepted): %.1fx\n", $overhead);
