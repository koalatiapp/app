<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Subscription\Plan\BusinessAnnualPlan;
use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\SmallTeamAnnualPlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloAnnualPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\PlanManager;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private PlanManager $planManager,
	) {
	}

	/**
	 * @Route("/account/subscription", name="manage_subscription")
	 */
	public function manageSubscription(): Response
	{
		$user = $this->getUser();
		$plan = $this->planManager->getPlanFromEntity($user);
		$plans = [
			new SoloPlan(),
			new SmallTeamPlan(),
			new BusinessPlan(),
			new SoloAnnualPlan(),
			new SmallTeamAnnualPlan(),
			new BusinessAnnualPlan(),
		];

		$upcomingPlan = null;

		if ($user->getUpcomingSubscriptionPlan() &&
			$user->getSubscriptionPlan() != $user->getUpcomingSubscriptionPlan() &&
			$user->getSubscriptionChangeDate() >= new DateTime()) {
			$upcomingPlan = $this->planManager->getPlanFromUniqueName($user->getUpcomingSubscriptionPlan());
		}

		return $this->render('app/user/subscription.html.twig', [
			'currentPlan' => $plan,
			'upcomingPlan' => $upcomingPlan,
			'upcomingPlanChangeDate' => $user->getSubscriptionChangeDate(),
			'plans' => $plans,
		]);
	}
}
