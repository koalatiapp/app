<?php

namespace App\Api\State;

use App\Entity\Testing\Recommendation;

/**
 * @extends AbstractDoctrineStateWrapper<Recommendation>
 */
class RecommendationProcessor extends AbstractDoctrineStateWrapper
{
	/**
	 * Hook before the persistence of a resource in the database.
	 *
	 * @param Recommendation $recommendation
	 */
	protected function prePersist(object &$recommendation, ?array $originalData): void
	{
		if (!$recommendation->getIsCompleted()) {
			$recommendation->setCompletedBy(null);
		} elseif (!$originalData['isCompleted']) {
			$recommendation->setCompletedBy($this->getUser());
		}
	}
}
