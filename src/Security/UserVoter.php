<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
	final public const VIEW = 'user_view';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attribute)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof User;
	}

	/**
	 * @param ?User $user
	 */
	protected function voteOnAttribute(string $attribute, mixed $user, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW])) {
			throw new \Exception("Undefined user voter attribute: $attribute");
		}

		/** @var User */
		$loggedInUser = $token->getUser();

		if ($loggedInUser === $user) {
			return true;
		}

		$ressourcesInCommon = array_uintersect(
			[
				...$user->getOrganizations(),
				...$user->getAllProjects(),
			],
			[
				...$loggedInUser->getOrganizations(),
				...$loggedInUser->getAllProjects(),
			],
			fn ($valueA, $valueB) => $valueA === $valueB ? 0 : -1,
		);

		return count($ressourcesInCommon) > 0;
	}
}
