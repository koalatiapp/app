<?php

namespace App\Util;

use App\Repository\OrganizationInvitationRepository;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Checks if the visitor was refered to Koalati via a valid organization
 * invitation link based on its session variables.
 */
class InvitationValidator
{
	private SessionInterface $session;

	public function __construct(
		private readonly RouterInterface $router,
		private readonly OrganizationInvitationRepository $invitationRepository,
		private readonly HashidsInterface $idHasher,
		RequestStack $requestStack,
	) {
		$this->session = $requestStack->getSession();
	}

	public function userWasReferedByValidInvitation(): bool
	{
		$targetUrl = $this->session->get('_security.main.target_path');

		if (!$targetUrl) {
			return false;
		}

		$targetRequest = Request::create($targetUrl);

		try {
			$route = $this->router->match($targetRequest->getPathInfo());
		} catch (\Exception) {
			return false;
		}

		if ($route['_route'] != "organization_invitation_accept") {
			return false;
		}

		$id = $this->idHasher->decode($route["id"])[0];
		$invitation = $this->invitationRepository->find($id);

		if (!$invitation || $invitation->getHash() != $route["hash"] || $invitation->isUsed() || $invitation->hasExpired()) {
			return false;
		}

		return true;
	}
}
