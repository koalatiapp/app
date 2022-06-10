<?php

namespace App\Subscription;

use App\Entity\Organization;
use App\Entity\User;
use App\Exception\NonExistantSubscriptionPlanException;
use App\Subscription\Plan\FreePlan;
use App\Subscription\Plan\PlanInterface;

class PlanManager
{
	/**
	 * @var array<string,PlanInterface> Plan instances, indexed by their unique name
	 */
	private array $plans = [];

	/**
	 * @param iterable<mixed,PlanInterface> $availablePlans
	 */
	public function __construct(iterable $availablePlans)
	{
		foreach ($availablePlans as $plan) {
			$this->plans[$plan->getUniqueName()] = $plan;
		}
	}

	/**
	 * @return array<string,PlanInterface> Plan instances, indexed by their unique name
	 */
	public function getAvailablePlans(): array
	{
		return $this->plans;
	}

	public function getPlanFromUniqueName(string $planUniqueName): PlanInterface
	{
		return $this->plans[$planUniqueName];
	}

	public function getPlanFromEntity(User|Organization $entity): PlanInterface
	{
		$user = $entity;

		if ($entity instanceof Organization) {
			$user = $entity->getOwner();
		}

		$planUniqueName = $user->getSubscriptionPlan();

		if (!$planUniqueName) {
			return new FreePlan();
		}

		return $this->getPlanFromUniqueName($planUniqueName);
	}

	/**
	 * @throws NonExistantSubscriptionPlanException
	 */
	public function getPlanFromPaddleId(int $paddleId): PlanInterface
	{
		foreach ($this->plans as $plan) {
			if ($plan->getPaddleId() == $paddleId) {
				return $plan;
			}
		}

		throw new NonExistantSubscriptionPlanException('No Paddle subscription plan was found for ID %s');
	}
}
