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
		private EntityManagerInterface $em
	) {
	}

	public function deleteRelations(OrganizationMember $membership): EntityManagerInterface
	{
		$organization = $membership->getOrganization();

		if ($organization) {
			$organization->removeMember($membership);

			if ($organization->getMembers()->count() <= 1) {
				$this->em->remove($organization);
			}
		}

		return $this->em;
	}
}
