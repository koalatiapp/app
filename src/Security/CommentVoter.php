<?php

namespace App\Security;

use App\Entity\Comment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
	final public const VIEW = 'comment_view';
	final public const RESOLVE = 'comment_resolve';

	public function __construct(
		private readonly ProjectVoter $projectVoter,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Comment;
	}

	/**
	 * @param Comment $comment
	 */
	protected function voteOnAttribute(string $attribute, mixed $comment, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::RESOLVE])) {
			throw new \Exception("Undefined project voter attribute: $attribute");
		}

		return $this->projectVoter->voteOnAttribute(
			ProjectVoter::PARTICIPATE,
			$comment->getChecklistItem()->getChecklist()->getProject(),
			$token
		);
	}
}
