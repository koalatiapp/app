<?php

namespace App\Util\DeletionManager;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;

class ProjectDeletionManager
{
	private CollectionDeletionManager $collectionDeletionManager;

	public function __construct(
		private EntityManagerInterface $em
	) {
		$this->collectionDeletionManager = new CollectionDeletionManager($em);
	}

	public function deleteRelations(Project $project): EntityManagerInterface
	{
		// Delete checklist
		$checklist = $project->getChecklist();
		$this->collectionDeletionManager->deleteItems($checklist->getItems());
		$this->collectionDeletionManager->deleteItems($checklist->getItemGroups());
		$this->em->remove($checklist);

		$this->collectionDeletionManager->deleteItems($project->getIgnoreEntries());
		$this->collectionDeletionManager->deleteItems($project->getTeamMembers());
		$this->collectionDeletionManager->deleteItems($project->getRecommendations());
		$this->collectionDeletionManager->deleteItems($project->getPages());

		$this->em->remove($project);

		return $this->em;
	}
}
