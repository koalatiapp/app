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
	 * @var int PAGE_TEST_QUOTA Quota of page tests that are included for free in this plan
	 */
	public const PAGE_TEST_QUOTA = 150_000;

	/**
	 * @var float COST_PER_ADDITIONAL_PAGE_TEST Cost of each additional page test after the quota has been reached, in US dollars
	 */
	public const COST_PER_ADDITIONAL_PAGE_TEST = 0.00079861;

	/**
	 * @var int MAX_ACTIVE_PAGES_PER_PROJECT Maximum number of active pages a project can have
	 */
	public const MAX_ACTIVE_PAGES_PER_PROJECT = PHP_INT_MAX;

	/**
	 * @var int MAX_TEAM_OWNED Maximum numbers of team a user can own
	 */
	public const MAX_TEAM_OWNED = 100;
}
