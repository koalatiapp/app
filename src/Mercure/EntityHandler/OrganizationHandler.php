<?php

namespace App\Mercure\EntityHandler;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Mercure\EntityHandlerInterface;
use App\Mercure\MercureEntityInterface;

class OrganizationHandler implements EntityHandlerInterface
{
	public function getSupportedEntity(): string
	{
		return Organization::class;
	}

	public function getType(): string
	{
		return "Organization";
	}

	/**
	 * @param Organization $organization
	 */
	public function getAffectedUsers(MercureEntityInterface $organization): array
	{
		return $organization->getMembers()->map(fn (OrganizationMember $member = null) => $member->getUser())->toArray();
	}
}
