<?php

namespace App\Subscription\Plan;

class SmallTeamPlan extends SoloPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'SmallTeam';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '664975';

	/**
	 * @var int PAGE_TEST_QUOTA Quota of page tests that are included for free in this plan
	 */
	public const PAGE_TEST_QUOTA = 100;

	/**
	 * @var int MAX_ACTIVE_PAGES_PER_PROJECT Maximum number of active pages a project can have
	 */
	public const MAX_ACTIVE_PAGES_PER_PROJECT = 100;

	/**
	 * @var int MAX_PROJECT_MEMBERS Maximum numbers of members a user can have in their project
	 */
	public const MAX_PROJECT_MEMBERS = PHP_INT_MAX;

	/**
	 * @var int MAX_TEAM_OWNED Maximum numbers of team a user can own
	 */
	public const MAX_TEAM_OWNED = 1;
}
