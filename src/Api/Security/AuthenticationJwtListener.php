<?php

namespace App\Api\Security;

use App\Entity\User;
use App\Subscription\PlanManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthenticationJwtListener
{
	public function __construct(
		private PlanManager $planManager,
	) {
	}

	public function onJwtCreated(JWTCreatedEvent $event): void
	{
		/** @var User $user */
		$user = $event->getUser();

		if (!$user->isVerified()) {
			throw new AccessDeniedException("You may not use the Koalati API because your email address has not been verified.");
		}

		$plan = $this->planManager->getPlanFromEntity($user);

		if (!$plan->hasApiAccess()) {
			// Check if a parent organization has access to the API...
			foreach ($user->getOrganizationLinks() as $organizationLink) {
				$organization = $organizationLink->getOrganization();
				$organizationPlan = $this->planManager->getPlanFromEntity($organization);

				if ($organizationPlan->hasApiAccess()) {
					return;
				}
			}

			throw new AccessDeniedException("You may not use the Koalati API because your subscription plan does not permit it.");
		}
	}
}
