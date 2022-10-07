<?php

namespace App\Subscription\Plan;

use App\Util\SelfHosting;

abstract class AbstractPlan implements PlanInterface
{
	public function __construct(
		private ?SelfHosting $selfHosting = null
	) {
	}

	/**
	 * @return string Plan unique name
	 */
	public function getUniqueName(): string
	{
		return static::UNIQUE_NAME;
	}

	/**
	 * @return string ID of this plan in Paddle
	 */
	public function getPaddleId(): string
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return '';
		}

		return static::PADDLE_ID;
	}

	/**
	 * @return int Maximum number of active pages a project can have
	 */
	public function getMaxActivePagesPerProject(): int
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return PHP_INT_MAX;
		}

		return static::MAX_ACTIVE_PAGES_PER_PROJECT;
	}

	/**
	 * @return int Maximum numbers of team a user can own
	 */
	public function getMaxTeamOwned(): int
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return PHP_INT_MAX;
		}

		return static::MAX_TEAM_OWNED;
	}

	/**
	 * @return int Maximum numbers of members a user can have in their project
	 */
	public function getMaxProjectMembers(): int
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return PHP_INT_MAX;
		}

		return static::MAX_PROJECT_MEMBERS;
	}

	/**
	 * @return bool Whether the user has access to the Checklist feature
	 */
	public function hasChecklistAccess(): bool
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return true;
		}

		return static::HAS_CHECKLIST_ACCESS;
	}

	/**
	 * @return bool Whether the user has access to the testing features
	 */
	public function hasTestingAccess(): bool
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return true;
		}

		return static::HAS_TESTING_ACCESS;
	}

	/**
	 * @return bool Whether the user has access to the monitoring features
	 */
	public function hasMonitoringAccess(): bool
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return true;
		}

		return static::HAS_MONITORING_ACCESS;
	}

	public function isUpgradeComparedTo(PlanInterface $comparativePlan): bool
	{
		if ($this instanceof TrialPlan) {
			return $comparativePlan instanceof NoPlan;
		}

		if ($comparativePlan instanceof TrialPlan) {
			return get_class($this) != NoPlan::class;
		}

		return is_subclass_of($this, $comparativePlan::class);
	}

	public function isDowngradeComparedTo(PlanInterface $comparativePlan): bool
	{
		if ($this instanceof TrialPlan) {
			return !($comparativePlan instanceof NoPlan);
		}

		if ($comparativePlan instanceof TrialPlan) {
			return get_class($this) == NoPlan::class;
		}

		return is_subclass_of($comparativePlan, $this::class);
	}

	public function isPaidPlan(): bool
	{
		if ($this->selfHosting?->isSelfHosted()) {
			return false;
		}

		return true;
	}

	public function isAnnualPlan(): bool
	{
		return str_ends_with($this->getUniqueName(), "Annual");
	}

	public function __toString()
	{
		return static::UNIQUE_NAME;
	}
}
