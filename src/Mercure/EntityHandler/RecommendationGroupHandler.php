<?php

namespace App\Mercure\EntityHandler;

use App\Entity\Project;
use App\Mercure\EntityHandlerInterface;
use App\Mercure\MercureEntityInterface;
use App\Util\Testing\RecommendationGroup;

class RecommendationGroupHandler implements EntityHandlerInterface
{
	public function getSupportedEntity(): string
	{
		return RecommendationGroup::class;
	}

	public function getType(): string
	{
		return "RecommendationGroup";
	}

	/**
	 * @param RecommendationGroup $recommendationGroup
	 */
	public function getAffectedUsers(MercureEntityInterface $recommendationGroup): array
	{
		$projectHandler = new ProjectHandler();
		$projects = [];

		foreach ($recommendationGroup->getRecommendations() as $recommendation) {
			$project = $recommendation->getProject();
			$projects[$project->getId()] = $project;
		}

		return array_merge(
			...array_map(fn (Project $project) => $projectHandler->getAffectedUsers($project), $projects)
		);
	}
}
