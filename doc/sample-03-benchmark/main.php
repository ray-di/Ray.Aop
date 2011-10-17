<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Weaver;
ob_start();

$billing0 = new Weaver(new RealBillingService, array());
$billing1 = new Weaver(new RealBillingService, array(new EmptyInterceptor));
$billing2 = new Weaver(new RealBillingService, array(new EmptyInterceptor, new EmptyInterceptor));
$billing3 = new Weaver(new RealBillingService, array(new EmptyInterceptor, new EmptyInterceptor, new EmptyInterceptor));
$billing4 = new Weaver(new RealBillingService, array(new EmptyInterceptor, new EmptyInterceptor, new EmptyInterceptor, new EmptyInterceptor));
//
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
