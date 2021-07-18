<?php

namespace App\Controller\Api\Organization;

use App\Controller\Api\AbstractApiController;
use App\Entity\Organization;
use App\Entity\OrganizationInvitation;
use App\Entity\OrganizationMember;
use App\Repository\OrganizationMemberRepository;
use App\Security\OrganizationVoter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/api/organization/members", name="api_organization_members_")
 */
class MembersController extends AbstractApiController
{
	/**
	 * @Route("", methods={"GET","HEAD"}, name="list", options={"expose": true})
	 */
	public function list(Request $request): JsonResponse
	{
		$organizationId = $request->query->get('organization_id');

		if (!$organizationId) {
			return $this->apiError('You must provide a valid value for `organization_id`.');
		}

		$organization = $this->getOrganization($organizationId);

		return $this->apiSuccess($organization->getMembersSortedByRole());
	}

	/**
	 * @Route("/{id}", methods={"DELETE"}, name="delete", options={"expose": true})
	 */
	public function delete(int $id, OrganizationMemberRepository $organizationMemberRepository): JsonResponse
	{
		$membership = $organizationMemberRepository->find($id);

		if (!$this->isGranted(OrganizationVoter::MANAGE, $membership->getOrganization())) {
			return $this->accessDenied();
		}

		if ($membership->getHighestRole() == OrganizationMember::ROLE_ADMIN) {
			$hasOtherAdmins = false;

			foreach ($membership->getOrganization()->getMembers() as $otherMember) {
				if ($membership != $otherMember && $otherMember->getHighestRole() == OrganizationMember::ROLE_ADMIN) {
					$hasOtherAdmins = true;
					break;
				}
			}

			if (!$hasOtherAdmins) {
				return $this->apiError($this->translator->trans('organization.flash.member_remove_no_admins'));
			}
		}

		$em = $this->getDoctrine()->getManager();
		$em->remove($membership);
		$em->flush();

		return $this->apiSuccess([
			'message' => $this->translator->trans('organization.flash.member_removed_successfully', [
				'%user%' => $membership->getUser()->getFullName(),
				'%organization%' => $membership->getOrganization()->getName(),
			]),
		]);
	}

	/**
	 * @Route("/{id}/role", methods={"POST", "PUT"}, name="role", options={"expose": true})
	 */
	public function updateRole(Request $request, int $id, OrganizationMemberRepository $organizationMemberRepository): JsonResponse
	{
		$membership = $organizationMemberRepository->find($id);
		$newRole = $request->request->get('role');

		if (!$this->isGranted(OrganizationVoter::MANAGE, $membership->getOrganization())) {
			return $this->accessDenied();
		}

		if ($membership->getHighestRole() == OrganizationMember::ROLE_ADMIN && $newRole != OrganizationMember::ROLE_ADMIN) {
			$hasOtherAdmins = false;

			foreach ($membership->getOrganization()->getMembers() as $otherMember) {
				if ($membership != $otherMember && $otherMember->getHighestRole() == OrganizationMember::ROLE_ADMIN) {
					$hasOtherAdmins = true;
					break;
				}
			}

			if (!$hasOtherAdmins) {
				return $this->apiError($this->translator->trans('organization.flash.member_role_update_no_admins'));
			}
		}

		$membership->setHighestRole($newRole);

		$em = $this->getDoctrine()->getManager();
		$em->persist($membership);
		$em->flush();

		return $this->apiSuccess([
			'message' => $this->translator->trans('organization.flash.member_role_updated_successfully', [
				'%name%' => $membership->getUser()->getFullName(),
				'%role%' => $this->translator->trans('roles.'.$newRole),
			]),
		]);
	}

	/**
	 * @Route("/{id}/invitation", methods={"POST", "PUT"}, name="invite", options={"expose": true})
	 */
	public function sendInvitation(int $id, Request $request, MailerInterface $mailer, TranslatorInterface $translator): JsonResponse
	{
		$organization = $this->getOrganization($id, OrganizationVoter::MANAGE);
		$firstName = trim($request->request->get('first_name'));
		$email = strtolower(trim($request->request->get('email')));

		// Check if there's already a pending invitation for that email
		foreach ($organization->getOrganizationInvitations() as $invitation) {
			if (!$invitation->hasExpired() && !$invitation->isUsed() && $invitation->getEmail() == $email) {
				return $this->apiError($translator->trans('organization.flash.invitation_already_sent'));
			}
		}

		$invitation = new OrganizationInvitation($firstName, $email, $organization, $this->getUser());

		$em = $this->getDoctrine()->getManager();
		$em->persist($invitation);
		$em->flush();

		$email = (new TemplatedEmail())
			->to(new Address($email, $firstName))
			->subject($translator->trans('email.organization_invitation.subject', ['%inviter%' => $this->getUser()->getFullName(), '%organization%' => $organization]))
			->htmlTemplate('email/organization_invitation.html.twig')
			->context([
				'invitation' => $invitation,
			]);
		$mailer->send($email);

		return $this->apiSuccess();
	}
}