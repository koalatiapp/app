<?php

namespace App\Security;

use App\Entity\OrganizationMember;
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

		return $this->organizationVoter->voteOnAttribute($attribute, $member->getOrganization(), $token);
	}
}
