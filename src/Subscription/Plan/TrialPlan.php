<?php

namespace App\Subscription\Plan;

class TrialPlan extends SmallTeamPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'Trial';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '738679';

	/**
	 * @var int PAGE_TEST_QUOTA Quota of page tests that are included for free in this plan
	 */
	public const PAGE_TEST_QUOTA = 6_000;

	/**
	 * @var float COST_PER_ADDITIONAL_PAGE_TEST Cost of each additional page test after the quota has been reached, in US dollars
	 */
	public const COST_PER_ADDITIONAL_PAGE_TEST = 0.0;

	/**
	 * @var bool CAN_EXCEED_PAGE_TEST_QUOTA Whether the user can make additional page tests after reaching the quota included in their plan
	 */
	public const CAN_EXCEED_PAGE_TEST_QUOTA = false;
}
