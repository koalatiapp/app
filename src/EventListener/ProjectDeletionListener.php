<?php

namespace App\EventListener;

use App\Entity\Project;
use App\Util\DeletionManager\ProjectDeletionManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ProjectDeletionListener
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private ProjectDeletionManager $projectDeletionManager
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.event)
	 */
	public function preRemove(Project $project, LifecycleEventArgs $event): void
	{
		$em = $this->projectDeletionManager->deleteRelations($project);
		$em->flush();
	}
}
