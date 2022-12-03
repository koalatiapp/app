<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Provides subscription-related properties and methods
 * to the User entity class.
 */
trait UserSubscriptionTrait
{
	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $paddleUserId = null;

	#[ORM\Column(type: 'string', nullable: true)]
	private ?string $paddleSubscriptionId = null;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	#[Groups(['self'])]
	private ?string $subscriptionPlan = null;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $upcomingSubscriptionPlan = null;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $subscriptionChangeDate = null;

	#[ORM\Column(type: 'datetime', nullable: true)]
	private ?\DateTimeInterface $subscriptionRenewalDate = null;

	public function getPaddleUserId(): ?string
	{
		return $this->paddleUserId;
	}

	public function setPaddleUserId(?string $paddleUserId): static
	{
		$this->paddleUserId = $paddleUserId;

		return $this;
	}

	public function getPaddleSubscriptionId(): ?string
	{
		return $this->paddleSubscriptionId;
	}

	public function setPaddleSubscriptionId(?string $paddleSubscriptionId): static
	{
		$this->paddleSubscriptionId = $paddleSubscriptionId;

		return $this;
	}

	public function getSubscriptionPlan(): ?string
	{
		// If the subscription change date is past, the user is now on the upcoming plan.
		if ($this->getsubscriptionChangeDate() && $this->getsubscriptionChangeDate() < new \DateTime()) {
			return $this->getUpcomingSubscriptionPlan();
		}

		return $this->subscriptionPlan;
	}

	public function setSubscriptionPlan(?string $subscriptionPlan): static
	{
		$this->subscriptionPlan = $subscriptionPlan;

		return $this;
	}

	public function getUpcomingSubscriptionPlan(): ?string
	{
		return $this->upcomingSubscriptionPlan;
	}

	public function setUpcomingSubscriptionPlan(?string $upcomingSubscriptionPlan): static
	{
		$this->upcomingSubscriptionPlan = $upcomingSubscriptionPlan;

		return $this;
	}

	public function getSubscriptionChangeDate(): ?\DateTimeInterface
	{
		return $this->subscriptionChangeDate;
	}

	public function setSubscriptionChangeDate(?\DateTimeInterface $subscriptionChangeDate): static
	{
		$this->subscriptionChangeDate = $subscriptionChangeDate;

		return $this;
	}

	public function getSubscriptionRenewalDate(): ?\DateTimeInterface
	{
		return $this->subscriptionRenewalDate;
	}

	public function setSubscriptionRenewalDate(?\DateTimeInterface $subscriptionRenewalDate): static
	{
		$this->subscriptionRenewalDate = $subscriptionRenewalDate;

		return $this;
	}
}
