<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use App\Form\User\UserChangeEmailType;
use App\Form\User\UserChangePasswordType;
use App\Form\User\UserDeleteAccountType;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecuritySettingsController extends AbstractController
{
	private UserPasswordHasherInterface $passwordHasher;
	private UserAuthenticatorInterface $authenticator;
	private LoginFormAuthenticator $loginFormAuthenticator;
	private TokenStorageInterface $tokenStorage;

	/**
	 * @required
	 */
	public function setPasswordHasher(UserPasswordHasherInterface $passwordHasher): void
	{
		$this->passwordHasher = $passwordHasher;
	}

	/**
	 * @required
	 */
	public function setAuthenticators(UserAuthenticatorInterface $authenticator, LoginFormAuthenticator $loginFormAuthenticator, TokenStorageInterface $tokenStorage): void
	{
		$this->authenticator = $authenticator;
		$this->loginFormAuthenticator = $loginFormAuthenticator;
		$this->tokenStorage = $tokenStorage;
	}

	/**
	 * @Route("/account/security", name="manage_account_security")
	 */
	public function securitySettings(Request $request): Response
	{
		$passwordForm = $this->processPasswordForm($request);
		$emailForm = $this->processEmailForm($request);
		$deletionForm = $this->processDeletionForm($request);

		if ($deletionForm instanceof Response) {
			return $deletionForm;
		}

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

			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$this->addFlash('success', $this->translator->trans('user_settings.security.password.flash.success'));
		}

		return $passwordForm;
	}

	private function processEmailForm(Request $request): FormInterface | Response
	{
		$user = $this->getUser();
		$emailForm = $this->createForm(UserChangeEmailType::class, $user);
		$emailForm->handleRequest($request);

		if ($emailForm->isSubmitted() && $emailForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$token = new AnonymousToken('default', 'anon.');
			$this->tokenStorage->setToken($token);
			$request->getSession()->invalidate();

			$authenticatorReponse = $this->authenticator->authenticateUser($user, $this->loginFormAuthenticator, $request);
			$this->addFlash('success', $this->translator->trans('user_settings.security.email.flash.success'));

			return $authenticatorReponse;
		}

		return $emailForm;
	}

	private function processDeletionForm(Request $request): FormInterface | Response
	{
		$user = $this->getUser();
		$deletionForm = $this->createForm(UserDeleteAccountType::class, $user);
		$deletionForm->handleRequest($request);

		if ($deletionForm->isSubmitted() && $deletionForm->isValid()) {
			$em = $this->getDoctrine()->getManager();
			$em->remove($user);
			$em->flush();

			$token = new AnonymousToken('default', 'anon.');
			$this->tokenStorage->setToken($token);
			$request->getSession()->invalidate();

			return $this->redirectToRoute('login');
		}

		return $deletionForm;
	}
}
