<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
	private UserPasswordHasherInterface $passwordHasher;

	/**
	 * @required
	 */
	public function setPasswordHasher(UserPasswordHasherInterface $passwordHasher): void
	{
		$this->passwordHasher = $passwordHasher;
	}

	/**
	 * @Route("/sign-up", name="registration")
	 */
	public function signUp(Request $request, UserAuthenticatorInterface $authenticator, LoginFormAuthenticator $loginFormAuthenticator): Response
	{
		$user = new User();
		$form = $this->createForm(UserRegistrationType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$plainPassword = $form->get('password')->getData();
			$hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
			$user->setPassword($hashedPassword);

			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			return $authenticator->authenticateUser($user, $loginFormAuthenticator, $request);
		}

		return $this->render('public/registration.html.twig', ['form' => $form->createView()]);
	}
}
