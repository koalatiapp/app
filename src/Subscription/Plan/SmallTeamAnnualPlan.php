<?php

namespace App\Subscription\Plan;

class SmallTeamAnnualPlan extends SmallTeamPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	final public const UNIQUE_NAME = 'SmallTeamAnnual';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	final public const PADDLE_ID = '781499';
}
