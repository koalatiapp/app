<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Form\User\UserChangeEmailType;
use App\Form\User\UserChangePasswordType;
use App\Form\User\UserDeleteAccountType;
use App\Security\EmailVerifier;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecuritySettingsController extends AbstractController
{
	public function __construct(
		private readonly UserPasswordHasherInterface $passwordHasher,
		private readonly TokenStorageInterface $tokenStorage,
		private readonly EmailVerifier $emailVerifier,
	) {
	}

	#[Route(path: '/account/security', name: 'manage_account_security')]
	public function securitySettings(Request $request): Response
	{
		$deletionForm = $this->processDeletionForm($request);

		if ($deletionForm instanceof Response) {
			return $deletionForm;
		}

		$passwordForm = $this->processPasswordForm($request);
		$emailForm = $this->processEmailForm($request);

		if ($emailForm instanceof Response) {
			return $emailForm;
		}

		return $this->render('app/user/security.html.twig', [
				'passwordForm' => $passwordForm->createView(),
				'emailForm' => $emailForm->createView(),
				'deletionForm' => $deletionForm->createView(),
			]);
	}

	private function processPasswordForm(Request $request): FormInterface
	{
		$user = $this->getUser();
		$passwordForm = $this->createForm(UserChangePasswordType::class, $user);
		$passwordForm->handleRequest($request);

		if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
			$newPassword = $passwordForm->get('newPassword')->getData();
			$hashedPassword = $this->passwordHasher->hashPassword($this->getUser(), $newPassword);
			$user->setPassword($hashedPassword);

			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->addFlash('success', $this->translator->trans('user_settings.security.password.flash.success'));
		}

		return $passwordForm;
	}

	private function processEmailForm(Request $request): FormInterface|Response
	{
		$user = $this->getUser();
		$emailForm = $this->createForm(UserChangeEmailType::class, $user);
		$emailForm->handleRequest($request);

		if ($emailForm->isSubmitted() && $emailForm->isValid()) {
			// Force the user to verify the new email address
			$user->setIsVerified(false);

			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->tokenStorage->setToken(null);
			$request->getSession()->invalidate();

			$this->emailVerifier->sendEmailConfirmation($user);

			return $this->redirectToRoute("verify_email_pending");
		}

		return $emailForm;
	}

	private function processDeletionForm(Request $request): FormInterface|Response
	{
		$user = $this->getUser();
		$deletionForm = $this->createForm(UserDeleteAccountType::class, $user);
		$deletionForm->handleRequest($request);

		if ($deletionForm->isSubmitted() && $deletionForm->isValid()) {
			if ($user->getOwnedOrganization()) {
				$this->entityManager->remove($user->getOwnedOrganization());
			}

			$this->entityManager->remove($user);
			$this->entityManager->flush();

			$this->tokenStorage->setToken(null);
			$request->getSession()->invalidate();

			return $this->redirectToRoute('login');
		}

		return $deletionForm;
	}
}
