<?php

namespace App\Security;

use App\Entity\Organization;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Subscription\PlanManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizationVoter extends Voter
{
	final public const VIEW = 'view';
	final public const PARTICIPATE = 'participate';
	final public const MANAGE = 'manage';
	final public const OWN_ORGANIZATION = 'own_organization';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly PlanManager $planManager,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Organization
			|| $attribute == self::OWN_ORGANIZATION;
	}

	/**
	 * @param Organization $organization
	 */
	protected function voteOnAttribute(string $attribute, mixed $organization, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::PARTICIPATE, self::MANAGE, self::OWN_ORGANIZATION])) {
			throw new \Exception("Undefined organization voter attribute: $attribute");
		}

		$user = $token->getUser();

		// User must be logged in to access any project
		if (!$user instanceof User) {
			return false;
		}

		if ($attribute == self::OWN_ORGANIZATION) {
			return $this->canUserOwnOrganization($user);
		}

		$member = $organization->getMemberFromUser($user);

		if (!$member) {
			return false;
		}

		$organizationPlan = $this->planManager->getPlanFromEntity($organization);
		if ($organizationPlan->getMaxTeamOwned() == 0 && $attribute != self::VIEW) {
			return false;
		}

		$roleValue = $member->calculateRoleValue();

		$requiredRoleValue = match ($attribute) {
			self::VIEW => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_VISITOR],
			self::PARTICIPATE => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_MEMBER],
			self::MANAGE => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_ADMIN],
			default => false
		};

		return $roleValue >= $requiredRoleValue;
	}

	private function canUserOwnOrganization(User $user): bool
	{
		$subscriptionPlan = $this->planManager->getPlanFromEntity($user);

		return $subscriptionPlan->getMaxTeamOwned() > 0;
	}
}
