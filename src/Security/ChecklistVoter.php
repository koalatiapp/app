<?php

namespace App\Security;

use App\Entity\Checklist\Checklist;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ChecklistVoter extends Voter
{
	final public const VIEW = 'checklist_view';
	final public const EDIT = 'checklist_edit';

	public function __construct(
		private readonly ProjectVoter $projectVoter,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof Checklist;
	}

	/**
	 * @param Checklist $checklist
	 */
	protected function voteOnAttribute(string $attribute, mixed $checklist, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW, self::EDIT])) {
			throw new \Exception("Undefined project voter attribute: $attribute");
		}

		return $this->projectVoter->voteOnAttribute(
			ProjectVoter::PARTICIPATE,
			$checklist->getProject(),
			$token
		);
	}
}
