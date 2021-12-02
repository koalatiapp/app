<?php

namespace App\Subscription\Plan;

class BusinessPlan extends SmallTeamPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'Business';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '664976';

	/**
	 * @var int MAX_ACTIVE_PROJECTS Maximum number of active projects a user can have during a given month
	 */
	public const MAX_ACTIVE_PROJECTS = 100;

	/**
	 * @var int MAX_TEAM_OWNED Maximum numbers of team a user can own
	 */
	public const MAX_TEAM_OWNED = 100;
}
