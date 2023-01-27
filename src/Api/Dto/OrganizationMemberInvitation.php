<?php

namespace App\Api\Dto;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Api\State\OrganizationMemberInvitationProcessor;
use App\Entity\Organization;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * API-facing data-transfer object that allows API users to invite people to
 * join their organiation(s).
 */
#[ApiResource(
	openapiContext: ["tags" => ['Organization Member']],
	operations: [
		new Get(controller: NotFoundAction::class, read: false, status: 404, openapi: false),
		new Post(
			uriTemplate: "/organization_members/invite",
			denormalizationContext: ["groups" => "organization_member_invitation.write"],
			processor: OrganizationMemberInvitationProcessor::class,
			status: 201,
		),
	]
)]
class OrganizationMemberInvitation
{
	#[Groups(['organization_member_invitation.write'])]
	public ?Organization $organization = null;

	#[Groups(['organization_member_invitation.write'])]
	public ?string $email = null;

	#[Groups(['organization_member_invitation.write'])]
	public ?string $firstName = null;
}
