<?php

namespace App\Subscription;

use App\Entity\ProjectActivityRecord;
use App\Entity\User;
use App\Repository\ProjectActivityRecordRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UsageManager
{
	/**
	 * Two or more page tests occuring in a span of `PAGE_TEST_BATCHING_TIMESPAN`
	 * seconds or less will be conted as a single Page Test for usage quota
	 * purposes.
	 */
	private const PAGE_TEST_BATCHING_TIMESPAN = 30;

	private User $user;

	public function __construct(
		private PlanManager $planManager,
		private ProjectActivityRecordRepository $projectActivityRepository,
		Security $security,
	) {
		/** @var User */
		$user = $security->getUser();
		$this->user = $user;
	}

	public function withUser(User $user): self
	{
		$clonedUsageManager = clone $this;
		$clonedUsageManager->user = $user;

		return $clonedUsageManager;
	}

	public function getUsageCycleStartDate(\DateTimeInterface|string|null $fromDate = null): \DateTimeImmutable
	{
		$cycleStartDate = $this->user->getPreviousBillingDate();

		if (!$cycleStartDate) {
			$cycleStartDate = \DateTimeImmutable::createFromInterface($this->user->getDateCreated());
			$cycleStartDate = $cycleStartDate->setTime(0, 0);
			$oneMonthAgo = new \DateTime("-1 month");

			while ($cycleStartDate < $oneMonthAgo) {
				$cycleStartDate = $cycleStartDate->modify("+1 month");
			}
		}

		$cycleStartDate = \DateTimeImmutable::createFromInterface($cycleStartDate);

		// If a date was specified, move backwards/forwards until that cycle is reached
		if ($fromDate !== null) {
			if (is_string($fromDate)) {
				$fromDate = new \DateTime($fromDate);
			}

			$fromDate = \DateTimeImmutable::createFromInterface($fromDate);
			$fromDate = $fromDate->setTime(0, 0);

			if ($fromDate < $cycleStartDate) {
				while ($fromDate < $cycleStartDate) {
					$cycleStartDate = $cycleStartDate->modify("-1 month");
				}
			} elseif ($fromDate > $cycleStartDate) {
				while ($fromDate > $cycleStartDate->modify("+1 month")) {
					$cycleStartDate = $cycleStartDate->modify("+1 month");
				}
			}
		}

		return $cycleStartDate;
	}

	public function getUsageCycleEndDate(\DateTimeInterface|string|null $fromDate = null): ?\DateTimeImmutable
	{
		return $this->getUsageCycleStartDate($fromDate)->modify("+1 month")->modify("-1 day")->setTime(23, 59, 59);
	}

	public function getUsageCycleBillingDate(\DateTimeInterface|string|null $fromDate = null): ?\DateTimeImmutable
	{
		return $this->getUsageCycleEndDate($fromDate)?->modify("+1 day");
	}

	/**
	 * Returns all Project Activity Records for the given user since the
	 * beginning of the billing cycle.
	 *
	 * @return array<int,ProjectActivityRecord>
	 */
	public function getUsageCycleRecords(\DateTimeInterface|string|null $fromDate = null): array
	{
		$cycleStartDate = $this->getUsageCycleStartDate($fromDate);

		return $this->projectActivityRepository->findAllForUserInCycle($this->user, $cycleStartDate);
	}

	/**
	 * Returns the number of "Page Test" units that have been used since the
	 * beginning of the billing cycle.
	 *
	 * @return int Number of Page Test units used in the cycle
	 */
	public function getPageTestUsage(\DateTimeInterface|string|null $fromDate = null): int
	{
		$testingRequests = $this->getUsageCycleRecords($fromDate);
		$usageUnits = 0;
		$batchingWindowEndTimeByUrl = [];

		foreach ($testingRequests as $testingRequest) {
			$url = $testingRequest->getPageUrl();
			$timestamp = $testingRequest->getDateCreated()->getTimestamp();
			$batchingEndTime = $batchingWindowEndTimeByUrl[$url] ?? null;

			if ($batchingEndTime === null || $batchingEndTime < $timestamp) {
				$usageUnits++;
				$batchingWindowEndTimeByUrl[$url] = $timestamp + self::PAGE_TEST_BATCHING_TIMESPAN;
			}
		}

		return $usageUnits;
	}

	/**
	 * Returns the estimated cost for the over-the-quota usage for the billing
	 * cycle.
	 *
	 * @return float cost in US dollars
	 */
	public function getUsageCostEstimate(\DateTimeInterface|string|null $fromDate = null): float
	{
		$unitsOverQuota = $this->getPageTestUsage($fromDate) - $this->getPageTestQuota();

		if ($unitsOverQuota <= 0) {
			return 0;
		}

		$costPerPageTest = $this->planManager->getPlanFromEntity($this->user)::COST_PER_ADDITIONAL_PAGE_TEST;

		return $unitsOverQuota * $costPerPageTest;
	}

	public function getPageTestQuota(): int
	{
		return $this->planManager->getPlanFromEntity($this->user)::PAGE_TEST_QUOTA;
	}
}
