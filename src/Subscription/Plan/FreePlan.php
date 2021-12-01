<?php

namespace App\Subscription\Plan;

class FreePlan extends AbstractPlan
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = 'Free';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '738679';

	/**
	 * @var bool HAS_CHECKLIST_ACCESS Whether the user has access to the Checklist feature
	 */
	public const HAS_CHECKLIST_ACCESS = true;

	// @TODO: Remove all of the privileges defined below. These are only meant to be granted until the subscription system is enabled.

	/**
	 * @var int MAX_ACTIVE_PROJECTS Maximum number of active projects a user can have during a given month
	 */
	public const MAX_ACTIVE_PROJECTS = 1000;

	/**
	 * @var int MAX_PROJECT_MEMBERS Maximum numbers of members a user can have in their project
	 */
	public const MAX_PROJECT_MEMBERS = PHP_INT_MAX;

	/**
	 * @var bool HAS_TESTING_ACCESS Whether the user has access to the testing features
	 */
	public const HAS_TESTING_ACCESS = true;

	/**
	 * @var bool HAS_MONITORING_ACCESS Whether the user has access to the monitoring features
	 */
	public const HAS_MONITORING_ACCESS = true;
}
