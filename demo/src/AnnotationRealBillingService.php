<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

use const PHP_EOL;

class AnnotationRealBillingService implements BillingService
{
    /**
     * @WeekendBlock
     */
    public function chargeOrder()
    {
        echo 'Charged.' . PHP_EOL;
    }
}
