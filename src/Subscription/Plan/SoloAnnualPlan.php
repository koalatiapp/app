<?php

namespace App\Subscription\Plan;

class SoloAnnualPlan extends SoloPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'SoloAnnual';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '781498';
}
