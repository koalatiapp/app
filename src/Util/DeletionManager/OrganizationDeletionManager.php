<?php

namespace App\Util\DeletionManager;

use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;

class OrganizationDeletionManager
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private EntityManagerInterface $em
	) {
	}

	public function deleteRelations(Organization $organization): EntityManagerInterface
	{
		$collectionDeletionManager = new CollectionDeletionManager($this->em);
		$collectionDeletionManager->deleteItems($organization->getIgnoreEntries());
		$collectionDeletionManager->deleteItems($organization->getOrganizationInvitations());
		$collectionDeletionManager->deleteItems($organization->getMembers());

		return $this->em;
	}
}
