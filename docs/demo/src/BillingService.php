<?php

namespace Ray\Aop\Demo;

interface BillingService
{
    /**
     * @WeekendBlock
     * @return void
     */
    public function chargeOrder();
}
