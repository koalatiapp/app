<?php

namespace App\Subscription\Plan;

class BusinessAnnualPlan extends BusinessPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'BusinessAnnual';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '781503';
}
