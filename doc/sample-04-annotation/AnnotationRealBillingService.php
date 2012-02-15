<?php

namespace Ray\Aop\Sample;

use Ray\Aop\Sample\Annotation\WeekendBlock;

class AnnotationRealBillingService implements BillingService {

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