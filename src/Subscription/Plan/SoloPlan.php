<?php

namespace App\Subscription\Plan;

class SoloPlan extends NoPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'Solo';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '664974';

	/**
	 * @var int MAX_ACTIVE_PAGES_PER_PROJECT Maximum number of active pages a project can have
	 */
	public const MAX_ACTIVE_PAGES_PER_PROJECT = 30;

	/**
	 * @var int MAX_PROJECT_MEMBERS Maximum numbers of members a user can have in their project
	 */
	public const MAX_PROJECT_MEMBERS = 0;

	/**
	 * @var bool HAS_CHECKLIST_ACCESS Whether the user has access to the Checklist feature
	 */
	public const HAS_CHECKLIST_ACCESS = true;

	/**
	 * @var bool HAS_TESTING_ACCESS Whether the user has access to the testing features
	 */
	public const HAS_TESTING_ACCESS = true;

	/**
	 * @var bool HAS_MONITORING_ACCESS Whether the user has access to the monitoring features
	 */
	public const HAS_MONITORING_ACCESS = true;

	public function isPaidPlan(): bool
	{
		return true;
	}
}
