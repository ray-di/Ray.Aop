<?php

namespace Ray\Aop\Sample;

interface BillingService {

	/**
	 * @var Receipt
	 *
	 * @WeekendBlock
	 */
	public function chargeOrder();
}