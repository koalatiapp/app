<?php

namespace App\Subscription;

use App\Entity\User;
use App\Subscription\Plan\FreePlan;
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

		if (in_array($newPlan->getUniqueName(), [FreePlan::UNIQUE_NAME, TrialPlan::UNIQUE_NAME])) {
			$this->cancelSubscription($user);

			return;
		}

		$this->paddleApi->subscription()->updateUser((int) $subscriptionId, [
			"plan_id" => $newPlan->getPaddleId(),
		]);
	}

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
