<?php

namespace App\Api\State;

use App\Entity\Organization;
use App\Entity\OrganizationMember;

/**
 * @extends AbstractDoctrineStateWrapper<Organization>
 */
class OrganizationProcessor extends AbstractDoctrineStateWrapper
{
	/**
	 * Hook before the removal of a resource in the database.
	 *
	 * @param Organization $organization
	 */
	protected function preRemove(object &$organization): void
	{
		foreach ($organization->getMembers() as $member) {
			$this->entityManager->remove($member);
		}

		foreach ($organization->getProjects() as $project) {
			$this->entityManager->remove($project);
		}
	}

	/**
	 * Hook before the persistence of a resource in the database.
	 *
	 * @param Organization $organization
	 */
	protected function prePersist(object &$organization, ?array $originalData): void
	{
		if (!$organization->getId()) {
			$member = new OrganizationMember($organization, $this->getUser(), OrganizationMember::ROLE_OWNER);
			$this->entityManager->persist($member);
		}
	}
}
