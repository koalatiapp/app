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
	final public const VIEW = 'project_view';
	final public const PARTICIPATE = 'project_participate';
	final public const EDIT = 'project_edit';
	final public const CHECKLIST = 'project_checklist';
	final public const TESTING = 'project_testing';

	public function __construct(
		private readonly PlanManager $planManager
	) {
	}

	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Project || in_array($attribute, [self::VIEW, self::PARTICIPATE, self::EDIT, self::CHECKLIST, self::TESTING]);
	}

	/**
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.key)
	 *
	 * @param ?Project $project
	 */
	protected function voteOnAttribute(string $attribute, mixed $project, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::PARTICIPATE, self::EDIT, self::CHECKLIST, self::TESTING])) {
			throw new \Exception("Undefined project voter attribute: $attribute");
		}

		/** @var User */
		$user = $token->getUser();
		$owner = $project?->getOwner() ?: $user;
		$plan = $this->planManager->getPlanFromEntity($owner);

		// Allow user to create projects if they have a plan or are in an active organization
		if (!$project) {
			return $plan::class != NoPlan::class || $user->getOrganizationLinks()->exists(function (mixed $key, OrganizationMember $membership) {
				$plan = $this->planManager->getPlanFromEntity($membership->getOrganization());

				return $plan::class != NoPlan::class;
			});
		}

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
