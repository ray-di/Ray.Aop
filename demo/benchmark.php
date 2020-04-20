<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/src/FooClass_Optimized.php';

use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Aop\NullInterceptor;

class benchmark
{
    public function intercepted()
    {
    }

    public function noInterceptor()
    {
    }
}

$tmpDir = __DIR__ . '/tmp';
$compiler = new Compiler($tmpDir);
$bind = (new Bind)->bindInterceptors(
    'intercepted',        // method name
    [new NullInterceptor]  // interceptors
);

array_map('unlink', glob("{$tmpDir}/*.php"));
$max = 1000 * 1000;

compile:
    $t = microtime(true);
    /** @var FooClass $foo */
    $foo = $compiler->newInstance(FooClass::class, [], $bind);
    echo sprintf('%-16s%.8f[ms]', 'compile', (microtime(true) - $t) * 1000) . PHP_EOL;

initialize:
    $t = microtime(true);
    /* @var FooClass $foo */
    for ($i = 0; $i < $max; $i++) {
        $foo = $compiler->newInstance(FooClass::class, [], $bind);
    }
    echo sprintf('%-16s%.8f[ms]', 'initialize', microtime(true) - $t) . PHP_EOL;

intercepting:
    $t = microtime(true);
    for ($i = 0; $i < $max; $i++) {
        $foo->intercepted();
    }
    echo sprintf('%-16s%.8f[μs]', 'intercepting', microtime(true) - $t) . PHP_EOL;

optimized:
    $foo = new \Ray_Aop_Demo_Optimized();
    $foo->bindings = ['intercepted' => [new NullInterceptor]];
    $t = microtime(true);
    for ($i = 0; $i < $max; $i++) {
        $foo->intercepted();
    }
    echo sprintf('%-16s%.8f[μs]', 'optimized?', microtime(true) - $t) . PHP_EOL;

no_intercepting:
    $t = microtime(true);
    for ($i = 0; $i < $max; $i++) {
        $foo->noInterceptor();
    }
    // should be same with native_call
    echo sprintf('%-16s%.8f[μs]', 'no_intercepting', microtime(true) - $t) . PHP_EOL;

native_call:
    $bareFoo = new FooClass();
    $t = microtime(true);
    for ($i = 0; $i < $max; $i++) {
        $bareFoo->noInterceptor();
    }
    echo sprintf('%-16s%.8f[μs]', 'native_call', microtime(true) - $t) . PHP_EOL;

//compile         7.96413422[ms]
//initialize      2.08863711[ms]
//intercepting    1.37815809[μs]
//optimized       1.15834904[μs]
//no_intercepting 0.02713394[μs]
//native_call     0.02986598[μs]
