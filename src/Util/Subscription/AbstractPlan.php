<?php

namespace App\Util\Subscription;

use App\Entity\Organization;
use App\Entity\User;

abstract class AbstractPlan
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
	 * @var int MAX_ACTIVE_PROJECTS Maximum number of active projects a user can have during a given month
	 */
	public const MAX_ACTIVE_PROJECTS = 0;

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

	public static function getPlan(User | Organization $entity): static
	{
		$user = $entity;

		if ($entity instanceof Organization) {
			$user = $entity->getOwner();
		}

		$planUniqueName = $user->getSubscriptionPlan() ?: FreePlan::UNIQUE_NAME;
		$planClass = '\\App\\Util\\Subscription\\'.$planUniqueName.'Plan';

		return new $planClass();
	}

	public function __toString()
	{
		return static::UNIQUE_NAME;
	}
}
