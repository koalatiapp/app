<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Entity\Project;

/**
 * @extends AbstractEntityActivityLogger<Project>
 */
class ProjectLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return Project::class;
	}

	public function postPersist(object &$project, ?array $originalData): void
	{
		$this->log(
			type: ($originalData['id'] ?? null) ? "project_edit" : "project_create",
			organization: $project->getOwnerOrganization(),
			project: $project,
			target: $project,
		);
	}

	public function postRemove(object &$project): void
	{
		$this->log(
			type: "project_delete",
			organization: $project->getOwnerOrganization(),
			project: null,
			target: null,
			data: [
				'project' => $project->getName(),
			]
		);
	}
}
