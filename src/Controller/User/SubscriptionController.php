<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Repository\ProjectActivityRecordRepository;
use App\Util\Subscription\AbstractPlan;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
	/**
	 * @Route("/account/subscription", name="manage_subscription")
	 */
	public function manageSubscription(ProjectActivityRecordRepository $activityRecordRepository): Response
	{
		$user = $this->getUser();
		$activeProjectCount = $activityRecordRepository->getActiveProjectCount($user);
		$plan = AbstractPlan::getPlan($user);

		return $this->render('app/user/subscription.html.twig', [
			'activeProjectCount' => $activeProjectCount,
			'plan' => $plan,
		]);
	}
}
