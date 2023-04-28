<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Entity\Testing\Recommendation;

/**
 * @extends AbstractEntityActivityLogger<Recommendation>
 */
class RecommendationLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return Recommendation::class;
	}

	public function postPersist(object &$recommendation, ?array $originalData): void
	{
		if ($recommendation->getIsCompleted() && !$originalData['isCompleted']) {
			$this->log(
				type: "recommendation_complete",
				organization: $recommendation->getProject()->getOwnerOrganization(),
				project: $recommendation->getProject(),
				target: $recommendation,
			);
		}
	}

	public function postRemove(object &$recommendation): void
	{
	}
}
