<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Api\Dto\OrganizationMemberInvitation;

/**
 * @extends AbstractEntityActivityLogger<OrganizationMemberInvitation>
 */
class OrganizationMemberInvitationLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return OrganizationMemberInvitation::class;
	}

	/** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
	public function postPersist(object &$invitation, ?array $originalData): void
	{
		$this->log(
			type: "organization_member_invite",
			organization: $invitation->organization,
			target: $invitation->organization,
			data: ["name" => $invitation->firstName],
		);
	}

	/** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
	public function postRemove(object &$invitation): void
	{
	}
}
