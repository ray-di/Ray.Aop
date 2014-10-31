<?php

namespace Ray\Aop\Demo;

class RealBillingService implements BillingService
{
    /**
     * @WeekendBlock
     */
    public function chargeOrder()
    {
        echo "Charged.\n";
    }
}
