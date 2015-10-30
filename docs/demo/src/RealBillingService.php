<?php

namespace Ray\Aop\Demo;

class RealBillingService implements BillingService
{
    public function chargeOrder()
    {
        echo "Charged.\n";
    }
}
