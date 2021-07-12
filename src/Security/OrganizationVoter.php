<?php

namespace App\Security;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizationVoter extends Voter
{
	public const VIEW = 'view';
	public const PARTICIPATE = 'participate';
	public const MANAGE = 'manage';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private OrganizationRepository $organizationRepository
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Organization;
	}

	/**
	 * @param Organization $organization
	 */
	protected function voteOnAttribute(string $attribute, mixed $organization, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::PARTICIPATE, self::MANAGE])) {
			throw new Exception("Undefined organization voter attribute: $attribute");
		}

		$user = $token->getUser();

		// User must be logged in to access any project
		if (!$user instanceof User) {
			return false;
		}

		$member = $organization->getMemberFromUser($user);
		$roleValue = $member->calculateRoleValue();

		$requiredRoleValue = match ($attribute) {
			self::VIEW => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_VISITOR],
			self::PARTICIPATE => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_MEMBER],
			self::MANAGE => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_ADMIN],
			default => false
		};

		return $roleValue >= $requiredRoleValue;
	}
}
