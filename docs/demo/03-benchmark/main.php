<?php

namespace Ray\Aop;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Sample\EmptyInterceptor;
use Ray\Aop\Sample\RealBillingService;

$compiler = new Compiler($_ENV['TMP_DIR']);
$billing0 = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], new Bind);
$billing1 = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], (new Bind)->bindInterceptors('chargeOrder', []));
$billing2 = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], (new Bind)->bindInterceptors('chargeOrder', [new EmptyInterceptor]));
$billing3 = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], (new Bind)->bindInterceptors('chargeOrder', [new EmptyInterceptor, new EmptyInterceptor]));
$billing4 = $compiler->newInstance('Ray\Aop\Sample\RealBillingService', [], (new Bind)->bindInterceptors('chargeOrder', [new EmptyInterceptor, new EmptyInterceptor, new EmptyInterceptor]));


$echo = function ($index, $time) {
    $time = ((microtime(true) - $time) * 1000);
    printf('%s Time:%01.4f msec ', $index, $time) . PHP_EOL;
};

$mTime = microtime(true);
(new RealBillingService)->chargeOrder();
$echo('original ', $mTime);

$mTime = microtime(true);
$billing0->chargeOrder();
$echo("0 aspect ", $mTime);

$mTime = microtime(true);
$billing1->chargeOrder();
$echo("1 aspect ", $mTime);

$mTime = microtime(true);
$billing2->chargeOrder();
$echo("2 aspects", $mTime);

$mTime = microtime(true);
$billing3->chargeOrder();
$echo("3 aspects", $mTime);

$mTime = microtime(true);
$billing4->chargeOrder();
$echo("4 aspects", $mTime);

echo PHP_EOL;
