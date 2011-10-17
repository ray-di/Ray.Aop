<?php

namespace Ray\Aop\Sample;

require dirname(__DIR__) . '/bootstrap.php';

use Ray\Aop\Weaver;

$weavedBilling = new Weaver(new RealBillingService, array(new Timer, new intercepterA, new intercepterB));
try {
    $weavedBilling->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}