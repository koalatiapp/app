<?php

namespace App\Security;

use App\Entity\OrganizationMember;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizationMemberVoter extends Voter
{
	final public const VIEW = 'view';
	final public const EDIT = 'edit';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly OrganizationVoter $organizationVoter,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof OrganizationMember;
	}

	/**
	 * @param OrganizationMember $member
	 */
	protected function voteOnAttribute(string $attribute, mixed $member, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::EDIT])) {
			throw new \Exception("Undefined organization voter attribute: $attribute");
		}

		$organization = $member->getOrganization();

		// Check for organization-level access first
		if (!$this->organizationVoter->voteOnAttribute($attribute, $organization, $token)) {
			return false;
		}

		// Esure that only the owner of the organization can edit its own member
		if ($attribute == self::EDIT && $member->getHighestRole() == OrganizationMember::ROLE_OWNER) {
			/** @var User */
			$user = $token->getUser();
			$currentUserMember = $organization->getMemberFromUser($user);

			if ($currentUserMember->getHighestRole() != OrganizationMember::ROLE_OWNER) {
				return false;
			}
		}

		return true;
	}
}
