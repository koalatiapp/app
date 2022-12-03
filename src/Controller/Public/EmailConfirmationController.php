<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use App\Util\Analytics\AnalyticsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class EmailConfirmationController extends AbstractController
{
	public function __construct(
		private readonly EmailVerifier $emailVerifier,
		private readonly AnalyticsInterface $analytics,
	) {
	}

	#[Route(path: '/verify-email/pending', name: 'verify_email_pending')]
	public function verifyEmailPending(): Response
	{
		return $this->render("public/email_confirmation_pending.html.twig");
	}

	#[Route(path: '/verify-email/check', name: 'verify_email')]
	public function verifyUserEmail(Request $request, UserRepository $userRepository, UserAuthenticatorInterface $authenticator, LoginFormAuthenticator $loginFormAuthenticator): Response
	{
		if ($this->getUser()) {
			return $this->redirectToRoute("dashboard");
		}

		$id = $request->get('id');

		if ($id === null) {
			return $this->redirectToRoute('registration');
		}

		$user = $userRepository->find($id);

		if ($user === null) {
			return $this->redirectToRoute('registration');
		}

		// Validate email confirmation link (sets User::isVerified=true and persists if successful)
		try {
			$this->emailVerifier->handleEmailConfirmation($request, $user);
		} catch (VerifyEmailExceptionInterface $exception) {
			$this->addFlash('verify_email_error', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

			return $this->redirectToRoute('registration');
		}

		$this->analytics->trackEvent("Validated email address");
		$this->addFlash('success', $this->translator->trans('registration.flash.email_confirmed'));

		$authenticator->authenticateUser($user, $loginFormAuthenticator, $request);

		return $this->redirectToRoute("dashboard");
	}
}
