<?php

namespace App\Security;

use App\Entity\Page;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PageVoter extends Voter
{
	final public const VIEW = 'page_view';
	final public const EDIT = 'page_edit';

	public function __construct(
		private readonly ProjectVoter $projectVoter,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Page;
	}

	/**
	 * @param Page $page
	 */
	protected function voteOnAttribute(string $attribute, mixed $page, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::EDIT])) {
			throw new \Exception("Undefined project voter attribute: $attribute");
		}

		return $this->projectVoter->voteOnAttribute(
			$attribute == self::EDIT ? ProjectVoter::EDIT : ProjectVoter::VIEW,
			$page->getProject(),
			$token
		);
	}
}
