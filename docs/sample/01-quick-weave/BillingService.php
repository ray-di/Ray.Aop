<?php

namespace Ray\Aop\Sample;

interface BillingService
{
    /**
     * @WeekendBlock
     * @return void
     */
    public function chargeOrder();
}
