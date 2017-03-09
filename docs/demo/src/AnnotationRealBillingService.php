<?php
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
