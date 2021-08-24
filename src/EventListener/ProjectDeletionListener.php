<?php

namespace App\EventListener;

use App\Entity\Project;
use App\Util\DeletionManager\ProjectDeletionManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ProjectDeletionListener
{
	public function preRemove(Project $project, LifecycleEventArgs $event): void
	{
		$projectDeletionManager = new ProjectDeletionManager($event->getEntityManager());
		$projectDeletionManager->deleteRelations($project);
	}
}
