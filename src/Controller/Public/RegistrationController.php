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
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
	public function __construct(
		private readonly UserPasswordHasherInterface $passwordHasher,
		private readonly AnalyticsInterface $analytics,
		private readonly EmailVerifier $emailVerifier,
	) {
	}

	#[Route(path: '/sign-up', name: 'registration')]
	public function signUp(Request $request, SelfHosting $selfHosting, MailerInterface $mailer): Response
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
					$user->setSubscriptionChangeDate(new \DateTime('+14 days'))
							->setUpcomingSubscriptionPlan(NoPlan::UNIQUE_NAME);
				}

				$this->entityManager->persist($user);
				$this->entityManager->flush();

				// Request that the users validates their email address
				$this->emailVerifier->sendEmailConfirmation($user);

				// Send Welcome email
				try {
					$email = (new TemplatedEmail())
							->to(new Address($user->getEmail(), $user->getFirstName()))
							->subject($this->translator->trans('email.welcome.subject'))
							->htmlTemplate('email/welcome.html.twig')
							->context([
								'user' => $user,
							]);
					$mailer->send($email);
				} catch (\Exception $exception) {
					$this->logger->warning($exception->getMessage(), $exception->getTrace());
				}

				$this->analytics->trackEvent("Sign up");

				return $this->redirectToRoute("verify_email_pending");
			}
		}

		return $this->render('public/registration.html.twig', ['form' => $form->createView()]);
	}
}
