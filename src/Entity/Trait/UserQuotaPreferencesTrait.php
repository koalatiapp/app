<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

/**
 * Provides quote preferences properties and methods
 * to the User entity class.
 */
trait UserQuotaPreferencesTrait
{
	/**
	 * Whether the user allows new Page Tests to be requested once their quota
	 * has been reached. If this is activated, this may incur additional costs
	 * to the user (up to the limit defined by `quotaExceedanceSpendingLimit`).
	 */
	#[ORM\Column(type: 'boolean', options: ["default" => false])]
	private bool $allowsPageTestsOverQuota = false;

	/**
	 * Spending limit (in USD) for page tests over the user's included quota.
	 */
	#[ORM\Column(type: 'float', nullable: true)]
	private ?float $quotaExceedanceSpendingLimit = null;

	/**
	 * Whether the user allows new Page Tests to be requested once their quota
	 * has been reached. If this is activated, this may incur additional costs
	 * to the user (up to the limit defined by `quotaExceedanceSpendingLimit`).
	 */
	public function allowsPageTestsOverQuota(): bool
	{
		return $this->allowsPageTestsOverQuota;
	}

	/**
	 * Whether the user allows new Page Tests to be requested once their quota
	 * has been reached. If this is activated, this may incur additional costs
	 * to the user (up to the limit defined by `quotaExceedanceSpendingLimit`).
	 */
	public function setAllowsPageTestsOverQuota(bool $allowsPageTestsOverQuota): static
	{
		$this->allowsPageTestsOverQuota = $allowsPageTestsOverQuota;

		return $this;
	}

	/**
	 * Spending limit (in USD) for page tests over the user's included quota.
	 */
	public function getQuotaExceedanceSpendingLimit(): ?float
	{
		return $this->quotaExceedanceSpendingLimit;
	}

	/**
	 * Spending limit (in USD) for page tests over the user's included quota.
	 */
	public function setQuotaExceedanceSpendingLimit(?float $quotaExceedanceSpendingLimit): static
	{
		$this->quotaExceedanceSpendingLimit = $quotaExceedanceSpendingLimit;

		return $this;
	}
}
