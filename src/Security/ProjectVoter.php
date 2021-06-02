<?php

namespace App\Security;

use App\Entity\Project;
use App\Entity\User;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProjectVoter extends Voter
{
	public const VIEW = 'view';
	public const PARTICIPATE = 'participate';
	public const MANAGE = 'manage';

	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Project;
	}

	/**
	 * @param Project $project
	 */
	protected function voteOnAttribute(string $attribute, mixed $project, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::PARTICIPATE, self::MANAGE])) {
			throw new Exception("Undefined project voter attribute: $attribute");
		}

		$user = $token->getUser();

		// User must be logged in to access any project
		if (!$user instanceof User) {
			return false;
		}

		// Project has the user in its team
		if ($project->getTeamMembers()->map(fn ($member) => $member->getUser())->contains($user)) {
			return true;
		}

		// Project is owned by the user
		if ($project->getOwnerUser() == $user) {
			return true;
		}

		// Project belongs to the user's organization
		if ($project->getOwnerOrganization() &&
			$project->getOwnerOrganization()->getMembers()->map(fn ($member) => $member->getUser())->contains($user)) {
			return true;
		}

		return false;
	}
}
