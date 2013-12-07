<?php

namespace Ray\Aop\Sample;

use Ray\Aop\Sample\Annotation\WeekendBlock;

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
