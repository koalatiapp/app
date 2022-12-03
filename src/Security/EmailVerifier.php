<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
	public function __construct(
		private readonly VerifyEmailHelperInterface $verifyEmailHelper,
		private readonly MailerInterface $mailer,
		private readonly EntityManagerInterface $entityManager,
		private readonly TranslatorInterface $translator,
	) {
	}

	public function sendEmailConfirmation(User $user): void
	{
		$email = (new TemplatedEmail())
			->to($user->getEmail())
			->subject($this->translator->trans('email.email_validation.subject'))
			->htmlTemplate('email/email_confirmation.html.twig');

		$signatureComponents = $this->verifyEmailHelper->generateSignature(
			"verify_email",
			(string) $user->getId(),
			$user->getEmail(),
			['id' => $user->getId()]
		);

		$context = $email->getContext();
		$context['signedUrl'] = $signatureComponents->getSignedUrl();
		$context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
		$context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();
		$context['user'] = $user;

		$email->context($context);

		$this->mailer->send($email);
	}

	/**
	 * @throws VerifyEmailExceptionInterface
	 */
	public function handleEmailConfirmation(Request $request, User $user): void
	{
		$this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), (string) $user->getId(), $user->getEmail());

		$user->setIsVerified(true);

		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}
}
