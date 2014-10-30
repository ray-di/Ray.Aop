<?php
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

require __DIR__ . '/01-implicit-bind/BillingService.php';
require __DIR__ . '/01-implicit-bind/RealBillingService.php';
require __DIR__ . '/01-implicit-bind/WeekendBlocker.php';

require __DIR__ . '/02-multiple-interceptors/interceptorA.php';
require __DIR__ . '/02-multiple-interceptors/interceptorB.php';
require __DIR__ . '/02-multiple-interceptors/Timer.php';

require __DIR__ . '/03-benchmark/EmptyInterceptor.php';

require __DIR__ . '/04-annotation/WeekendBlock.php';
require __DIR__ . '/04-annotation/AnnotationRealBillingService.php';

require __DIR__ . '/05-my-matcher/IsContainsMatcher.php';

$_ENV['TMP_DIR'] = __DIR__ . '/tmp';
