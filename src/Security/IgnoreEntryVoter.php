<?php

namespace App\Security;

use App\Entity\OrganizationMember;
use App\Entity\Testing\IgnoreEntry;
use App\Entity\User;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class IgnoreEntryVoter extends Voter
{
	public const VIEW = 'view';
	public const DELETE = 'delete';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.security)
	 */
	public function __construct(
		private Security $security
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof IgnoreEntry;
	}

	/**
	 * @param IgnoreEntry $entry
	 */
	protected function voteOnAttribute(string $attribute, mixed $entry, TokenInterface $token): bool
	{
		$this->validateAttribute($attribute);
		$user = $token->getUser();

		// User must be logged in to access ignore entries
		if (!$user instanceof User) {
			return false;
		}

		switch ($entry->getScopeType()) {
			case 'project':
			case 'page':
				return $this->checkProjectPrivileges($entry, $attribute);

			case 'organization':
				return $this->checkOrganizationPrivileges($entry, $user);

			case 'user':
				return $user == $entry->getTargetUser();
		}

		return false;
	}

	/**
	 * @throws Exception
	 */
	private function validateAttribute(string $attribute): void
	{
		if (!in_array($attribute, [self::VIEW, self::DELETE])) {
			throw new Exception("Undefined project voter attribute: $attribute");
		}
	}

	private function checkProjectPrivileges(IgnoreEntry $entry, string $attribute): bool
	{
		$project = $entry->getTargetProject() ?: $entry->getTargetPage()?->getProject();
		$projectRole = $attribute == self::VIEW ? ProjectVoter::VIEW : ProjectVoter::MANAGE;

		return $this->security->isGranted($projectRole, $project);
	}

	private function checkOrganizationPrivileges(IgnoreEntry $entry, User $user): bool
	{
		$userRoles = $entry->getTargetOrganization()->getUserRoles($user);
		$hasRequiredRoles = (bool) array_intersect([OrganizationMember::ROLE_MEMBER, OrganizationMember::ROLE_ADMIN], $userRoles);

		return $hasRequiredRoles;
	}
}
