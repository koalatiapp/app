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
	final public const EDIT = 'edit';
	final public const CREATE = 'create';

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
			|| $attribute == self::CREATE;
	}

	/**
	 * @param Organization $organization
	 */
	protected function voteOnAttribute(string $attribute, mixed $organization, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::PARTICIPATE, self::EDIT, self::CREATE])) {
			throw new \Exception("Undefined organization voter attribute: $attribute");
		}

		$user = $token->getUser();

		// User must be logged in to access any project
		if (!$user instanceof User) {
			return false;
		}

		if ($attribute == self::CREATE) {
			return $this->canUserOwnOrganization($user);
		}

		$member = $organization->getMemberFromUser($user);

		if (!$member) {
			return false;
		}

		$roleValue = $member->calculateRoleValue();

		$requiredRoleValue = match ($attribute) {
			self::VIEW => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_VISITOR],
			self::PARTICIPATE => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_MEMBER],
			self::EDIT => OrganizationMember::ROLE_VALUES[OrganizationMember::ROLE_ADMIN],
		};

		return $roleValue >= $requiredRoleValue;
	}

	private function canUserOwnOrganization(User $user): bool
	{
		$subscriptionPlan = $this->planManager->getPlanFromEntity($user);

		return $subscriptionPlan->getMaxTeamOwned() > 0;
	}
}
