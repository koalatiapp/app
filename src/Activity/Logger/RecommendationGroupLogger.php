<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Util\Testing\RecommendationGroup;

/**
 * @extends AbstractEntityActivityLogger<RecommendationGroup>
 */
class RecommendationGroupLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return RecommendationGroup::class;
	}

	public function postPersist(object &$recommendationGroup, ?array $originalData): void
	{
		$this->log(
			type: "recommendation_group_complete",
			organization: $recommendationGroup->getProject()->getOwnerOrganization(),
			project: $recommendationGroup->getProject(),
			target: $recommendationGroup,
		);
	}

	public function postRemove(object &$recommendationGroup): void
	{
	}
}
