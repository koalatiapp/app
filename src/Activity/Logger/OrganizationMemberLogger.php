<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Entity\OrganizationMember;

/**
 * @extends AbstractEntityActivityLogger<OrganizationMember>
 */
class OrganizationMemberLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return OrganizationMember::class;
	}

	/** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
	public function postPersist(object &$membership, ?array $originalData): void
	{
		$this->log(
			type: "organization_member_update",
			organization: $membership->getOrganization(),
			target: $membership->getOrganization(),
			data: ["name" => $membership->getUser()->getFullName(), "role" => $membership->getHighestRole()],
		);
	}

	public function postRemove(object &$membership): void
	{
		$this->log(
			type: "organization_member_delete",
			organization: $membership->getOrganization(),
			target: $membership->getOrganization(),
			data: ["name" => $membership->getUser()->getFullName()],
		);
	}
}
