<?php
namespace Ray\Aop\Demo;

interface BillingService
{
    /**
     * @WeekendBlock
     */
    public function chargeOrder();
}
