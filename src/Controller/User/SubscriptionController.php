<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Repository\ProjectActivityRecordRepository;
use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\FreePlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloPlan;
use App\Subscription\PlanManager;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private ProjectActivityRecordRepository $activityRecordRepository,
		private PlanManager $planManager,
	) {
	}

	/**
	 * @Route("/account/subscription", name="manage_subscription")
	 */
	public function manageSubscription(): Response
	{
		$user = $this->getUser();
		$activeProjectCount = $this->activityRecordRepository->getActiveProjectCount($user);
		$plan = $this->planManager->getPlanFromEntity($user);
		$plans = [
			new FreePlan(),
			new SoloPlan(),
			new SmallTeamPlan(),
			new BusinessPlan(),
		];

		$upcomingPlan = null;

		if ($user->getUpcomingSubscriptionPlan() &&
			$user->getSubscriptionPlan() != $user->getUpcomingSubscriptionPlan() &&
			$user->getSubscriptionChangeDate() >= new DateTime()) {
			$upcomingPlan = $this->planManager->getPlanFromUniqueName($user->getUpcomingSubscriptionPlan());
		}

		return $this->render('app/user/subscription.html.twig', [
			'activeProjectCount' => $activeProjectCount,
			'activeProjectCountHistory' => $this->getHistoryUsageStats($user),
			'currentPlan' => $plan,
			'upcomingPlan' => $upcomingPlan,
			'upcomingPlanChangeDate' => $user->getSubscriptionChangeDate(),
			'plans' => $plans,
		]);
	}

	/**
	 * Calculates the number of active projects for each month in the requested timespan.
	 *
	 * @param User   $user           the user for which to calculate usage
	 * @param int    $numberOfMonths the number of months to rewind, starting from the start date
	 * @param string $startDate      The date at which to start calculating from. This should always be the first day of a month, at midnight.
	 *
	 * @return array<string,int> the index of each value is the month, formatted as `YYYY-MM` (`Y-m` in PHP date format)
	 */
	private function getHistoryUsageStats(User $user, int $numberOfMonths = 6, string $startDate = 'first day of this month midnight'): array
	{
		$monthlyUsage = [];
		$fromDate = new DateTimeImmutable($startDate);

		for ($i = 0; $i < $numberOfMonths; $i++) {
			$toDate = $fromDate->modify('+1 month')->modify('-1 minute');
			$monthIndex = $fromDate->format('Y-m');
			$monthlyUsage[$monthIndex] = $this->activityRecordRepository->getActiveProjectCount($user, $fromDate, $toDate);
			$fromDate = $fromDate->modify('-1 month');
		}

		return array_reverse($monthlyUsage, true);
	}
}
