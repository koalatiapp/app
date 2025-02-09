<?php

namespace App\Controller\User;

use App\Activity\Logger\UserLogger;
use App\Controller\AbstractController;
use App\Form\User\UserQuotaPreferencesType;
use App\Subscription\Plan\BusinessAnnualPlan;
use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\SmallTeamAnnualPlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloAnnualPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\PlanManager;
use App\Subscription\UsageManager;
use App\Util\SelfHosting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly PlanManager $planManager,
		private readonly UserLogger $userActivityLogger,
		SelfHosting $selfHosting,
	) {
		if ($selfHosting->isSelfHosted()) {
			throw new NotFoundHttpException("Subscriptions are not available on a self-hosted version of Koalati.");
		}
	}

	#[Route(path: '/account/subscription', name: 'manage_subscription')]
	public function manageSubscription(): Response
	{
		$user = $this->getUser();
		$plan = $this->planManager->getPlanFromEntity($user);
		$plans = [
			$this->planManager->getPlanFromUniqueName(SoloPlan::UNIQUE_NAME),
			$this->planManager->getPlanFromUniqueName(SmallTeamPlan::UNIQUE_NAME),
			$this->planManager->getPlanFromUniqueName(BusinessPlan::UNIQUE_NAME),
			$this->planManager->getPlanFromUniqueName(SoloAnnualPlan::UNIQUE_NAME),
			$this->planManager->getPlanFromUniqueName(SmallTeamAnnualPlan::UNIQUE_NAME),
			$this->planManager->getPlanFromUniqueName(BusinessAnnualPlan::UNIQUE_NAME),
		];

		$upcomingPlan = null;

		if ($user->getUpcomingSubscriptionPlan()
				&& $user->getSubscriptionPlan() != $user->getUpcomingSubscriptionPlan()
				&& $user->getSubscriptionChangeDate() >= new \DateTime()) {
			$upcomingPlan = $this->planManager->getPlanFromUniqueName($user->getUpcomingSubscriptionPlan());
		}

		return $this->render('app/user/subscription/subscription.html.twig', [
			'currentPlan' => $plan,
			'upcomingPlan' => $upcomingPlan,
			'upcomingPlanChangeDate' => $user->getSubscriptionChangeDate(),
			'plans' => $plans,
		]);
	}

	#[Route(path: '/account/subscription/quota', name: 'manage_subscription_quota')]
	public function manageSubscriptionQuota(Request $request, UsageManager $usageManager): Response
	{
		$user = $this->getUser();
		$form = $this->createForm(UserQuotaPreferencesType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->addFlash('success', $this->translator->trans('user_settings.quota.settings.flash.success'));

			$this->userActivityLogger->updateApiUsageSettings($user, [
				'allowsPageTestsOverQuota' => $user->allowsPageTestsOverQuota(),
				'quotaExceedanceSpendingLimit' => $user->getQuotaExceedanceSpendingLimit(),
			]);
		}

		return $this->render('app/user/subscription/quota.html.twig', [
			"form" => $form->createView(),
			"usageManager" => $usageManager,
		]);
	}
}
