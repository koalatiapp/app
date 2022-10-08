<?php

namespace App\Subscription;

use App\Entity\User;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\TrialPlan;
use Exception;
use Paddle\API as PaddleAPI;

class SubscriptionUpdater
{
	public function __construct(
		private PaddleAPI $paddleApi,
		private PlanManager $planManager
	) {
	}

	public function changePlan(User $user, string $newPlanName): void
	{
		$subscriptionId = $this->getSubscriptionId($user);
		$newPlan = $this->planManager->getPlanFromUniqueName($newPlanName);
		$currentPlan = $this->planManager->getPlanFromEntity($user);

		if ($newPlan instanceof TrialPlan) {
			$newPlan = $this->planManager->getPlanFromUniqueName(NoPlan::UNIQUE_NAME);
		}

		$isUpgrade = $newPlan->isUpgradeComparedTo($currentPlan);

		$this->paddleApi->subscription()->updateUser((int) $subscriptionId, [
			"plan_id" => $newPlan->getPaddleId(),
			"prorate" => $isUpgrade,
			"bill_immediately" => $isUpgrade,
		]);
	}

	/**
	 * Cancels the user's subscription on Paddle, effective immediately.
	 */
	public function cancelSubscription(User $user): void
	{
		$subscriptionId = $this->getSubscriptionId($user);
		$this->paddleApi->subscription()->cancelUser((int) $subscriptionId);
	}

	private function getSubscriptionId(User $user): string
	{
		if (!$user->getPaddleSubscriptionId()) {
			throw new Exception("Cannot update the plan of a user who doesn't have an existing Paddle Subscription ID`.");
		}

		return $user->getPaddleSubscriptionId();
	}
}
