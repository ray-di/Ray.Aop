<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Weaver,
    Ray\Aop\Bind;

$bind1 = new Bind;
$bind1->bindInterceptors('chargeOrder', array());
$bind1 = new Bind;
$bind1->bindInterceptors('chargeOrder', array(new EmptyInterceptor));
$bind2 = new Bind;
$bind2->bindInterceptors('chargeOrder', array(new EmptyInterceptor, new EmptyInterceptor));
$bind3 = new Bind;
$bind3->bindInterceptors('chargeOrder', array(new EmptyInterceptor, new EmptyInterceptor, new EmptyInterceptor));
$bind4 = new Bind;
$bind4->bindInterceptors('chargeOrder', array(new EmptyInterceptor, new EmptyInterceptor, new EmptyInterceptor, new EmptyInterceptor));

$billing0 = new Weaver(new RealBillingService, new Bind);
$billing1 = new Weaver(new RealBillingService, $bind1);
$billing2 = new Weaver(new RealBillingService, $bind2);
$billing3 = new Weaver(new RealBillingService, $bind3);
$billing4 = new Weaver(new RealBillingService, $bind4);
//
ob_start();

echo "original\n";
$billing = new RealBillingService;
echo "Timer start\n";
$mtime = microtime(true);
$billing->chargeOrder();
$time = microtime(true) - $mtime;
echo "Timer stop, time is =[" . sprintf('%01.7f', $time) . "] sec\n\n";

echo "0 aspect\n";
echo "Timer start\n";
$mtime = microtime(true);
$billing0->chargeOrder();
$time = microtime(true) - $mtime;
echo "Timer stop, time is =[" . sprintf('%01.7f', $time) . "] sec\n\n";

echo "1 aspect\n";
echo "Timer start\n";
$mtime = microtime(true);
$billing1->chargeOrder();
$time = microtime(true) - $mtime;
echo "Timer stop, time is =[" . sprintf('%01.7f', $time) . "] sec\n\n";

echo "2 aspects\n";
echo "Timer start\n";
$mtime = microtime(true);
$billing2->chargeOrder();
$time = microtime(true) - $mtime;
echo "Timer stop, time is =[" . sprintf('%01.7f', $time) . "] sec\n\n";

echo "3 aspects\n";
echo "Timer start\n";
$mtime = microtime(true);
$billing3->chargeOrder();
$time = microtime(true) - $mtime;
echo "Timer stop, time is =[" . sprintf('%01.7f', $time) . "] sec\n\n";

echo "4 aspects\n";
echo "Timer start\n";
$mtime = microtime(true);
$billing4->chargeOrder();
$time = microtime(true) - $mtime;
echo "Timer stop, time is =[" . sprintf('%01.7f', $time) . "] sec\n\n";
