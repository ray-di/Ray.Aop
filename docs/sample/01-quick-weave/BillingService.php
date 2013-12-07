<?php

namespace Ray\Aop\Sample;

interface BillingService
{
    /**
     * @WeekendBlock
     */
    public function chargeOrder();
}
