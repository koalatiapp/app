<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Util\InvitationValidator;
use App\Util\SelfHosting;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
	#[Route(path: '/login', name: 'login')]
	public function login(AuthenticationUtils $authenticationUtils, SelfHosting $selfHosting, InvitationValidator $invitationValidator): Response
	{
		if ($this->getUser()) {
			return $this->redirectToRoute('dashboard');
		}

		$signupAllowed = true;

		if ($selfHosting->isInviteOnlyRegistration()) {
			// In invite-only mode, redirect to account creation when user comes in from an invitation
			if ($invitationValidator->userWasReferedByValidInvitation()) {
				return $this->redirectToRoute("registration");
			}

			// Disable signup when invite-only is enabled and user wasn't refered by an invitation
			$signupAllowed = false;
		}

		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('public/security/login.html.twig', [
				'last_username' => $lastUsername,
				'error' => $error,
				'signup_allowed' => $signupAllowed,
			]);
	}

	#[Route(path: '/logout', name: 'logout')]
	public function logout(): void
	{
	}
}
