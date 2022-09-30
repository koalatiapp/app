<?php

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Subscription\SubscriptionUpdater;
use App\Util\SelfHosting;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/user/subscription", name="api_user_subscription_")
 */
class SubscriptionController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	public function __construct(
		SelfHosting $selfHosting,
	) {
		if ($selfHosting->isSelfHosted()) {
			$this->apiError("Subscriptions are not available on a self-hosted version of Koalati.", 404);
		}
	}

	/**
	 * @Route("/change", methods={"POST","PUT"}, name="change_plan", options={"expose": true})
	 */
	public function changePlan(Request $request, SubscriptionUpdater $subscriptionUpdater): JsonResponse
	{
		$user = $this->getUser();
		$newPlanName = $request->request->get("plan");

		if (!$newPlanName) {
			return $this->badRequest('You must provide a valid value for `plan`.');
		}

		$subscriptionUpdater->changePlan($user, $newPlanName);

		return $this->apiSuccess();
	}

	/**
	 * @Route("/cancel", methods={"POST","PUT"}, name="cancel_plan", options={"expose": true})
	 */
	public function cancelPlan(SubscriptionUpdater $subscriptionUpdater): JsonResponse
	{
		$user = $this->getUser();
		$subscriptionUpdater->cancelSubscription($user);

		return $this->apiSuccess();
	}
}
