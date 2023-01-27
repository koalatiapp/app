<?php

namespace App\Api\Security;

use App\Entity\User;
use App\Subscription\PlanManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthenticationJwtListener
{
	public function __construct(
		private PlanManager $planManager,
		private RequestStack $requestStack,
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

	public function onJwtDecoded(JWTDecodedEvent $event): void
	{
		$request = $this->requestStack->getCurrentRequest();

		/*
		 * If the request contains a session cookie, check to make sure the
		 * session's user matches the JWT's user. If it does not, throw a 401
		 * to let the client know they have to update their authentication.
		 *
		 * This should never occur when using the API as usual, but it can
		 * happen if a user of the app logs out and then logs in with a
		 * different user before their client-stored JWT expires.
		 */
		if ($request->cookies->has(session_name())) {
			$session = $this->requestStack->getSession();

			if ($session->get('_security.last_username') && $event->getPayload()['username'] != $session->get('_security.last_username')) {
				if (!str_contains($request->headers->get('referer'), "/api/docs")) {
					throw new UnauthorizedHttpException("bearer", "User session does not match the provided JWT. Please re-authenticate or clear your session cookie.");
				}
			}
		}
	}
}
