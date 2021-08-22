<?php

namespace App\Util\DeletionManager;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserDeletionManager
{
	private CollectionDeletionManager $collectionDeletionManager;
	private ProjectDeletionManager $projectDeletionManager;

	public function __construct(
		private EntityManagerInterface $em
	) {
		$this->projectDeletionManager = new ProjectDeletionManager($em);
		$this->collectionDeletionManager = new CollectionDeletionManager($em);
	}

	public function deleteRelations(User $user): EntityManagerInterface
	{
		$this->collectionDeletionManager->deleteItems($user->getOrganizationLinks());

		foreach ($user->getPersonalProjects() as $project) {
			$this->em->remove($project);
		}

		return $this->em;
	}
}
