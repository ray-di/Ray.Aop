<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

class AnnotationRealBillingService implements BillingService
{
    /**
     * @WeekendBlock
     */
    public function chargeOrder()
    {
        echo "Charged.\n";
    }
}
