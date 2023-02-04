<?php

namespace App\Subscription\Model;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Api\State\PageTestUsageProvider;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Contains the data related to page test usage and quotas for the user's
 * current usage cycle.
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
#[ApiResource(operations: [
	new Get(
		openapiContext: ["tags" => ['Page Test Usage']],
		normalizationContext: ["groups" => "usage.read"],
		provider: PageTestUsageProvider::class,
		uriTemplate: "/usage/current",
	),
])]
class CurrentUsageCycle
{
	public function __construct(
		/**
		 * Date at which this usage cycle starts.
		 */
		#[Groups(["usage.read"])]
		public \DateTimeImmutable $usageCycleStartDate,

		/**
		 * Date at which this usage cycle ends.
		 */
		#[Groups(["usage.read"])]
		public \DateTimeImmutable $usageCycleEndDate,

		/**
		 * Date after which the over-the-quota usage for this cycle will be billed.
		 */
		#[Groups(["usage.read"])]
		public \DateTimeImmutable $usageCycleBillingDate,

		/**
		 * Number of "Page Test" units that have been used since the beginning of
		 * the billing cycle.
		 */
		#[Groups(["usage.read"])]
		public int $pageTestUsage,

		/**
		 * The estimated cost for the over-the-quota usage, in US Dollars.
		 */
		#[Groups(["usage.read"])]
		public float $usageCost,

		/**
		 * Total number of page tests that are included in the user's plan, and for
		 * which no additional charges are incurred.
		 */
		#[Groups(["usage.read"])]
		public int $pageTestQuota,

		/**
		 * Whether the page test quota has been reached/exceeded or not.
		 */
		#[Groups(["usage.read"])]
		public bool $pageTestQuotaReached,

		/**
		 * Whether the spending limit defined by the user has been reached/exceeded or not.
		 */
		#[Groups(["usage.read"])]
		public bool $spendingLimitReached,

		/**
		 * Number of pages that can still be tested for the current usage cycle,
		 * based on the user's usage level and quota preferences.
		 */
		#[Groups(["usage.read"])]
		public int $numberOfPageTestsAllowed,

		/**
		 * Whether page tests for a given usage cycle can exceed the built-in quota
		 * of the user's plan.
		 */
		#[Groups(["usage.read"])]
		public bool $pageTestsOverQuotaAllowed,

		/**
		 * The user's maximum spending limit (in USD) for over-the-quota usage.
		 */
		#[Groups(["usage.read"])]
		public ?float $spendingLimit,
	) {
	}
}
