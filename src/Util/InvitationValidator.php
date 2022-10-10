<?php

namespace App\Util;

use App\Repository\OrganizationInvitationRepository;
use Exception;
use Hashids\HashidsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Checks if the visitor was refered to Koalati via a valid organization
 * invitation link based on its session variables.
 */
class InvitationValidator
{
	public function __construct(
		private SessionInterface $session,
		private RouterInterface $router,
		private OrganizationInvitationRepository $invitationRepository,
		private HashidsInterface $idHasher,
	) {
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
		} catch (Exception $e) {
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
