<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Entity\OrganizationMember;

/**
 * @extends AbstractEntityActivityLogger<OrganizationMember>
 */
class OrganizationMemberInvitationLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return OrganizationMember::class;
	}

	public function postPersist(object &$invitation, ?array $originalData): void
	{
		$this->log(
			type: "organization_member_invite",
			organization: $invitation->getOrganization(),
			target: $invitation->getOrganization(),
			data: ["name" => $invitation->getFirstName()],
		);
	}

	public function postRemove(object &$invitation): void
	{
	}
}
