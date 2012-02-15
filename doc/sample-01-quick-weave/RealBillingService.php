<?php

namespace Ray\Aop\Sample;

class RealBillingService implements BillingService {

	/**
	 * @var Receipt
	 *
	 * @WeekendBlock
	 */
	public function chargeOrder()
	{
	    echo "Charged.\n";
	}
}