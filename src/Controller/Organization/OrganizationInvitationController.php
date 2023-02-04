<?php

namespace App\Controller\Organization;

use App\Controller\AbstractController;
use App\Entity\OrganizationMember;
use App\Repository\OrganizationInvitationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/team/invitation', name: 'organization_invitation_')]
class OrganizationInvitationController extends AbstractController
{
	#[Route(path: '/{id}/{hash}', name: 'accept')]
	public function acceptInvitation(int $id, string $hash, Request $request, OrganizationInvitationRepository $invitationRepository): Response
	{
		if (!$this->getUser()) {
			$request->getSession()->set("pre_redirect_organization_invitation_url", $request->getUri());

			return $this->redirectToRoute("login");
		}

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
		$this->entityManager->persist($invitation);
		$this->entityManager->persist($membership);
		$this->entityManager->flush();

		return $this->redirectToRoute('organization_dashboard', ['id' => $this->idHasher->encode($invitation->getOrganization()->getId())]);
	}
}
