<?php

namespace App\Security;

use App\Entity\Testing\TestResult;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TestResultVoter extends Voter
{
	final public const VIEW = 'test_result_view';

	public function __construct(
		private readonly ProjectVoter $projectVoter,
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.attributes)
	 */
	protected function supports(string $attribute, mixed $subject): bool
	{
		return $subject instanceof TestResult;
	}

	/**
	 * @param TestResult $testResult
	 */
	protected function voteOnAttribute(string $attribute, mixed $testResult, TokenInterface $token): bool
	{
		if (!in_array($attribute, [self::VIEW])) {
			throw new \Exception("Undefined test result voter attribute: $attribute");
		}

		foreach ($testResult->getRecommendations() as $recommendation) {
			if ($this->projectVoter->voteOnAttribute(ProjectVoter::VIEW, $recommendation->getProject(), $token)) {
				return true;
			}
		}

		return false;
	}
}
