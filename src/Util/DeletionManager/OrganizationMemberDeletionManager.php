<?php

namespace App\Util\DeletionManager;

use App\Entity\OrganizationMember;
use Doctrine\ORM\EntityManagerInterface;

class OrganizationMemberDeletionManager
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly EntityManagerInterface $em
	) {
	}

	public function deleteRelations(OrganizationMember $membership): EntityManagerInterface
	{
		// @TODO: Handle auto-deletion of organizations when all members have been deleted via SQL onDelete cascades (which is the likeliest scenario)
		$organization = $membership->getOrganization();

		if ($organization) {
			$organization->removeMember($membership);

			if ($organization->getMembers()->count() == 0) {
				$this->em->remove($organization);
			}
		}

		return $this->em;
	}
}
