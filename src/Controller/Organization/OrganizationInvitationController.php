<?php

namespace App\Controller\Organization;

use App\Controller\AbstractController;
use App\Entity\OrganizationMember;
use App\Repository\OrganizationInvitationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/team/invitation", name="organization_invitation_")
 */
class OrganizationInvitationController extends AbstractController
{
	/**
	 * @Route("/{id}/{hash}", name="accept")
	 */
	public function acceptInvitation(int $id, string $hash, OrganizationInvitationRepository $invitationRepository): Response
	{
		$invitation = $invitationRepository->find($id);

		if (!$invitation || $invitation->getHash() != $hash) {
			throw new NotFoundHttpException();
		}

		if ($invitation->isUsed()) {
			$this->addFlash('danger', 'organization_invitation.flash.error_already_used', ['%date%' => $invitation->getDateUsed()->format('Y-m-d')]);

			return $this->redirectToRoute('dashboard');
		}

		if ($invitation->hasExpired()) {
			$this->addFlash('danger', 'organization_invitation.flash.error_expired');

			return $this->redirectToRoute('dashboard');
		}

		// Check if the user is already in the organization
		if ($invitation->getOrganization()->getMemberFromUser($this->getUser())) {
			$this->addFlash('danger', 'organization_invitation.flash.error_already_a_member');

			return $this->redirectToRoute('dashboard');
		}

		// Everything looks good: add the user to the organization
		$membership = new OrganizationMember($invitation->getOrganization(), $this->getUser(), OrganizationMember::ROLE_MEMBER);
		$invitation->markAsUsed();
		$em = $this->getDoctrine()->getManager();
		$em->persist($invitation);
		$em->persist($membership);
		$em->flush();

		return $this->redirectToRoute('organization_dashboard', ['id' => $this->idHasher->encode($invitation->getOrganization()->getId())]);
	}
}
