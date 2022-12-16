<?php

namespace App\Security;

use App\Entity\OrganizationMember;
use App\Entity\Testing\IgnoreEntry;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class IgnoreEntryVoter extends Voter
{
	final public const VIEW = 'view';
	final public const DELETE = 'delete';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.security)
	 */
	public function __construct(
		private readonly Security $security
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

		return match ($entry->getScopeType()) {
			'project', 'page' => $this->checkProjectPrivileges($entry, $attribute),
			'organization' => $this->checkOrganizationPrivileges($entry, $user),
			'user' => $user == $entry->getTargetUser(),
			default => false,
		};
	}

	/**
	 * @throws \Exception
	 */
	private function validateAttribute(string $attribute): void
	{
		if (!in_array($attribute, [self::VIEW, self::DELETE])) {
			throw new \Exception("Undefined project voter attribute: $attribute");
		}
	}

	private function checkProjectPrivileges(IgnoreEntry $entry, string $attribute): bool
	{
		$project = $entry->getTargetProject() ?: $entry->getTargetPage()?->getProject();
		$projectRole = $attribute == self::VIEW ? ProjectVoter::VIEW : ProjectVoter::EDIT;

		return $this->security->isGranted($projectRole, $project);
	}

	private function checkOrganizationPrivileges(IgnoreEntry $entry, User $user): bool
	{
		$userRoles = $entry->getTargetOrganization()->getUserRoles($user);
		$hasRequiredRoles = (bool) array_intersect([OrganizationMember::ROLE_MEMBER, OrganizationMember::ROLE_ADMIN], $userRoles);

		return $hasRequiredRoles;
	}
}
