<?php

namespace App\Subscription\Plan;

interface PlanInterface
{
	/**
	 * @var string UNIQUE_NAME Plan unique name
	 */
	public const UNIQUE_NAME = '';

	/**
	 * @var string PADDLE_ID ID of this plan in Paddle
	 */
	public const PADDLE_ID = '';

	/**
	 * @var int PAGE_TEST_QUOTA Quota of page tests that are included for free in this plan
	 */
	public const PAGE_TEST_QUOTA = 0;

	/**
	 * @var float COST_PER_ADDITIONAL_PAGE_TEST Cost of each additional page test after the quota has been reached, in US dollars
	 */
	public const COST_PER_ADDITIONAL_PAGE_TEST = 0.00118056;

	/**
	 * @var bool CAN_EXCEED_PAGE_TEST_QUOTA Whether the user can make additional page tests after reaching the quota included in their plan
	 */
	public const CAN_EXCEED_PAGE_TEST_QUOTA = true;

	/**
	 * @var int MAX_ACTIVE_PAGES_PER_PROJECT Maximum number of active pages a project can have
	 */
	public const MAX_ACTIVE_PAGES_PER_PROJECT = 0;

	/**
	 * @var int MAX_TEAM_OWNED Maximum numbers of team a user can own
	 */
	public const MAX_TEAM_OWNED = 0;

	/**
	 * @var int MAX_PROJECT_MEMBERS Maximum numbers of members a user can have in their project
	 */
	public const MAX_PROJECT_MEMBERS = 0;

	/**
	 * @var bool HAS_CHECKLIST_ACCESS Whether the user has access to the Checklist feature
	 */
	public const HAS_CHECKLIST_ACCESS = false;

	/**
	 * @var bool HAS_TESTING_ACCESS Whether the user has access to the testing features
	 */
	public const HAS_TESTING_ACCESS = false;

	/**
	 * @var bool HAS_MONITORING_ACCESS Whether the user has access to the monitoring features
	 */
	public const HAS_MONITORING_ACCESS = false;

	/**
	 * @return string Plan unique name
	 */
	public function getUniqueName(): string;

	/**
	 * @return string of this plan in Paddle
	 */
	public function getPaddleId(): string;

	/**
	 * @return int Maximum number of active pages a project can have
	 */
	public function getMaxActivePagesPerProject(): int;

	/**
	 * @return int Maximum numbers of team a user can own
	 */
	public function getMaxTeamOwned(): int;

	/**
	 * @return int Maximum numbers of members a user can have in their project
	 */
	public function getMaxProjectMembers(): int;

	/**
	 * @return bool Whether the user has access to the Checklist feature
	 */
	public function hasChecklistAccess(): bool;

	/**
	 * @return bool Whether the user has access to the testing features
	 */
	public function hasTestingAccess(): bool;

	/**
	 * @return bool Whether the user has access to the monitoring features
	 */
	public function hasMonitoringAccess(): bool;

	/**
	 * @return bool Whether the user has access to the Koalati API
	 */
	public function hasApiAccess(): bool;

	public function isUpgradeComparedTo(PlanInterface $comparativePlan): bool;

	public function isDowngradeComparedTo(PlanInterface $comparativePlan): bool;

	public function isPaidPlan(): bool;

	public function isAnnualPlan(): bool;

	public function __toString();
}
