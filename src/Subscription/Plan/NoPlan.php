<?php

namespace App\Subscription\Plan;

class NoPlan extends AbstractPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'NoSubscription';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '738679';

	public function hasApiAccess(): bool
	{
		return false;
	}

	public function isPaidPlan(): bool
	{
		return false;
	}
}
