<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Security\EmailVerifier;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\TrialPlan;
use App\Util\Analytics\AnalyticsInterface;
use App\Util\SelfHosting;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
	public function __construct(
		private UserPasswordHasherInterface $passwordHasher,
		private AnalyticsInterface $analytics,
		private EmailVerifier $emailVerifier,
	) {
	}

	/**
	 * @Route("/sign-up", name="registration")
	 */
	public function signUp(Request $request, EntityManagerInterface $entityManager, SelfHosting $selfHosting): Response
	{
		$user = new User();
		$form = $this->createForm(UserRegistrationType::class, $user);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if (filter_var($user->getFirstName(), FILTER_VALIDATE_URL)) {
				$form->get('firstName')->addError(new FormError($this->translator->trans('generic.error.invalid_name')));
			}

			if ($form->isValid()) {
				// Prevent URLs while allowing URL-looking strings (ex.: Dr.Emile)
				$user->setFirstName(str_replace('.', ' ', $user->getFirstName()));

				$plainPassword = $form->get('password')->getData();
				$hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
				$user->setPassword($hashedPassword);

				// Start users on a 14 day trial
				$user->setSubscriptionPlan(TrialPlan::UNIQUE_NAME);
				if (!$selfHosting->isSelfHosted()) {
					$user->setSubscriptionChangeDate(new DateTime('+14 days'))
						->setUpcomingSubscriptionPlan(NoPlan::UNIQUE_NAME);
				}

				$entityManager->persist($user);
				$entityManager->flush();

				// Request that the users validates their email address
				$this->emailVerifier->sendEmailConfirmation($user);

				$this->analytics->trackEvent("Sign up");

				return $this->redirectToRoute("verify_email_pending");
			}
		}

		return $this->render('public/registration.html.twig', ['form' => $form->createView()]);
	}
}
