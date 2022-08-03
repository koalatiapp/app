<?php

namespace App\Controller\Public;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Form\UserRegistrationType;
use App\Security\LoginFormAuthenticator;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\TrialPlan;
use App\Util\Analytics\AnalyticsInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
	public function __construct(
		private UserPasswordHasherInterface $passwordHasher,
		private AnalyticsInterface $analytics,
	) {
	}

	/**
	 * @Route("/sign-up", name="registration")
	 */
	public function signUp(Request $request, UserAuthenticatorInterface $authenticator, LoginFormAuthenticator $loginFormAuthenticator, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
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
				$user->setSubscriptionPlan(TrialPlan::UNIQUE_NAME)
					->setSubscriptionChangeDate(new DateTime('+14 days'))
					->setUpcomingSubscriptionPlan(NoPlan::UNIQUE_NAME);

				$email = (new TemplatedEmail())
					->to(new Address($user->getEmail(), $user->getFirstName()))
					->subject($this->translator->trans('email.welcome.subject'))
					->htmlTemplate('email/welcome.html.twig')
					->context([
						'user' => $user,
					]);

				$mailSent = false;

				try {
					$mailer->send($email);
					$mailSent = true;
				} catch (HandlerFailedException $e) {
					$form->get('email')->addError(new FormError($this->translator->trans('registration.form.error.email_failed')));
					$this->logger->error($e);
				}

				if ($mailSent) {
					$entityManager->persist($user);
					$entityManager->flush();

					$this->analytics->trackEvent("Sign up");

					return $authenticator->authenticateUser($user, $loginFormAuthenticator, $request);
				}
			}
		}

		return $this->render('public/registration.html.twig', ['form' => $form->createView()]);
	}
}
