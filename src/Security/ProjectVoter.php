<?php

namespace App\Security;

use App\Entity\OrganizationMember;
use App\Entity\Project;
use App\Entity\ProjectMember;
use App\Entity\User;
use App\Subscription\Plan\NoPlan;
use App\Subscription\PlanManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
	final public const VIEW = 'view';
	final public const PARTICIPATE = 'participate';
	final public const MANAGE = 'manage';
	final public const CHECKLIST = 'checklist';
	final public const TESTING = 'testing';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.security)
	 */
	public function __construct(
		private readonly PlanManager $planManager
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Project;
	}

	/**
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 *
	 * @param Project $project
	 */
	protected function voteOnAttribute(string $attribute, mixed $project, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::PARTICIPATE, self::MANAGE, self::CHECKLIST, self::TESTING])) {
			throw new \Exception("Undefined project voter attribute: $attribute");
		}

		$plan = $this->planManager->getPlanFromEntity($project->getOwner());

		if ($plan::class == NoPlan::class && $attribute != self::VIEW) {
			return false;
		}

		if ($attribute == self::CHECKLIST) {
			return $plan->hasChecklistAccess();
		}

		if ($attribute == self::TESTING) {
			return $plan->hasTestingAccess();
		}

		$user = $token->getUser();

		// User must be logged in to access any project
		if (!$user instanceof User) {
			return false;
		}

		// Project has the user in its team
		if ($project->getTeamMembers()->map(fn (ProjectMember $member = null) => $member->getUser())->contains($user)) {
			return true;
		}

		// Project is owned by the user
		if ($project->getOwnerUser() == $user) {
			return true;
		}

		// Project belongs to the user's organization
		if ($project->getOwnerOrganization() &&
			$project->getOwnerOrganization()->getMembers()->map(fn (OrganizationMember $member = null) => $member->getUser())->contains($user)) {
			return true;
		}

		return false;
	}
}
