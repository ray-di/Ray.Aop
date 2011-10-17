<?php
require dirname(__DIR__) . '/src.php';

require __DIR__ . '/sample-01-manual-weave/BillingService.php';
require __DIR__ . '/sample-01-manual-weave/RealBillingService.php';
require __DIR__ . '/sample-01-manual-weave/WeekendBlocker.php';

require __DIR__ . '/sample-02-multiple-intercepters/intercepterA.php';
require __DIR__ . '/sample-02-multiple-intercepters/intercepterB.php';
require __DIR__ . '/sample-02-multiple-intercepters/Timer.php';

require __DIR__ . '/sample-03-benchmark/EmptyInterceptor.php';
