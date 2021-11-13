<?php

namespace App\Subscription\Plan;

abstract class AbstractPlan implements PlanInterface
{
	/**
	 * @return string UNIQUE_NAME Plan unique name
	 */
	public function getUniqueName(): string
	{
		return static::UNIQUE_NAME;
	}

	/**
	 * @return string PADDLE_ID ID of this plan in Paddle
	 */
	public function getPaddleId(): string
	{
		return static::PADDLE_ID;
	}

	/**
	 * @return int MAX_ACTIVE_PROJECTS Maximum number of active projects a user can have during a given month
	 */
	public function getMaxActiveProjects(): int
	{
		return static::MAX_ACTIVE_PROJECTS;
	}

	/**
	 * @return int MAX_TEAM_OWNED Maximum numbers of team a user can own
	 */
	public function getMaxTeamOwned(): int
	{
		return static::MAX_TEAM_OWNED;
	}

	/**
	 * @return int MAX_PROJECT_MEMBERS Maximum numbers of members a user can have in their project
	 */
	public function getMaxProjectMembers(): int
	{
		return static::MAX_PROJECT_MEMBERS;
	}

	/**
	 * @return bool HAS_CHECKLIST_ACCESS Whether the user has access to the Checklist feature
	 */
	public function hasChecklistAccess(): bool
	{
		return static::HAS_CHECKLIST_ACCESS;
	}

	/**
	 * @return bool HAS_TESTING_ACCESS Whether the user has access to the testing features
	 */
	public function hasTestingAccess(): bool
	{
		return static::HAS_TESTING_ACCESS;
	}

	/**
	 * @return bool HAS_MONITORING_ACCESS Whether the user has access to the monitoring features
	 */
	public function hasMonitoringAccess(): bool
	{
		return static::HAS_MONITORING_ACCESS;
	}

	public function isUpgradeComparedTo(PlanInterface $comparativePlan): bool
	{
		return is_subclass_of($this, $comparativePlan::class);
	}

	public function isDowngradeComparedTo(PlanInterface $comparativePlan): bool
	{
		return is_subclass_of($comparativePlan, $this::class);
	}

	public function __toString()
	{
		return static::UNIQUE_NAME;
	}
}
