<?php

namespace App\Controller\Api\Organization;

use App\Controller\Api\AbstractApiController;
use App\Entity\OrganizationMember;
use App\Repository\UserRepository;
use App\Security\OrganizationVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
	 * @Route("/role/{userId}", methods={"POST", "PUT"}, name="role", options={"expose": true})
	 */
	public function updateRole(Request $request, int $userId, UserRepository $userRepository): JsonResponse
	{
		$user = $userRepository->find($userId);
		$organizationId = $request->request->get('organizationId');
		$newRole = $request->request->get('role');

		if (!$organizationId) {
			return $this->apiError('You must provide a valid value for `organization_id`.');
		}

		$organization = $this->getOrganization($organizationId, OrganizationVoter::MANAGE);
		$membership = $organization->getMemberFromUser($user);

		if ($membership->getHighestRole() == OrganizationMember::ROLE_ADMIN && $newRole != OrganizationMember::ROLE_ADMIN) {
			$hasOtherAdmins = false;

			foreach ($organization->getMembers() as $otherMember) {
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

		return $this->apiSuccess();
	}
}
