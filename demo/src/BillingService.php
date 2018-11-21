<?php

declare(strict_types=1);

namespace Ray\Aop\Demo;

interface BillingService
{
    /**
     * @WeekendBlock
     */
    public function chargeOrder();
}
