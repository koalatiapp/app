<?php

namespace App\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Activity\ActivityLogger;
use App\Api\Dto\OrganizationMemberInvitation;
use App\Entity\Organization;
use App\Entity\OrganizationInvitation;
use App\Entity\User;
use App\Security\OrganizationVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @implements ProcessorInterface<OrganizationMemberInvitation, null>
 */
class OrganizationMemberInvitationProcessor implements ProcessorInterface
{
	public function __construct(
		private readonly Security $security,
		private readonly MailerInterface $mailer,
		private readonly TranslatorInterface $translator,
		private readonly EntityManagerInterface $entityManager,
		private readonly ActivityLogger $activityLogger,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function process($data, Operation $operation, array $uriVariables = [], array $context = []): ?object
	{
		if (!isset($data->organization)) {
			throw new BadRequestHttpException("Payload is missing 'project'.");
		}

		if (!isset($data->firstName)) {
			throw new BadRequestHttpException("Payload is missing 'first_name'.");
		}

		if (!isset($data->email)) {
			throw new BadRequestHttpException("Payload is missing 'email'.");
		}

		if (!$this->security->isGranted(OrganizationVoter::EDIT, $data->organization)) {
			throw new AccessDeniedHttpException("Access Denied.");
		}

		$organization = $data->organization;
		$firstName = trim($data->firstName);
		$email = strtolower(trim($data->email));

		if (!$firstName || filter_var($firstName, FILTER_VALIDATE_URL)) {
			throw new BadRequestHttpException($this->translator->trans('generic.error.invalid_name'));
		}

		$this->checkForExistingInvitations($organization, $email);

		// Prevent URLs while allowing URL-looking strings (ex.: Dr.Emile)
		$firstName = str_replace('.', ' ', $firstName);

		/** @var User */
		$user = $this->security->getUser();
		$invitation = new OrganizationInvitation($firstName, $email, $organization, $user);

		$this->entityManager->persist($invitation);
		$this->entityManager->flush();

		$this->sendWelcomeEmail($invitation);

		$this->activityLogger->postPersist($invitation, null);

		return null;
	}

	/**
	 * Checks if there's already a pending invitation for that email.
	 *
	 * @throws ConflictHttpException
	 */
	private function checkForExistingInvitations(Organization $organization, string $email): void
	{
		foreach ($organization->getOrganizationInvitations() as $invitation) {
			if (!$invitation->hasExpired() && !$invitation->isUsed() && $invitation->getEmail() == $email) {
				throw new ConflictHttpException($this->translator->trans('organization.flash.invitation_already_sent'));
			}
		}
	}

	private function sendWelcomeEmail(OrganizationInvitation $invitation): void
	{
		$email = (new TemplatedEmail())
				->to(new Address($invitation->getEmail(), $invitation->getFirstName()))
				->subject($this->translator->trans('email.organization_invitation.subject', [
					'%inviter%' => $invitation->getInviter()->getFullName(),
					'%organization%' => $invitation->getOrganization(),
				]))
				->htmlTemplate('email/organization_invitation.html.twig')
				->context(['invitation' => $invitation]);
		$this->mailer->send($email);
	}
}
