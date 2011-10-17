<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Weaver;

$weavedBilling = new Weaver(new RealBillingService, array(new WeekendBlocker));
try {
    echo $weavedBilling->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
