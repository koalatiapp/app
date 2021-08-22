<?php

namespace App\EventListener;

use App\Entity\User;
use App\Util\DeletionManager\UserDeletionManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserDeletionListener
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private UserDeletionManager $userDeletionManager
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.event)
	 */
	public function preRemove(User $user, LifecycleEventArgs $event): void
	{
		$em = $this->userDeletionManager->deleteRelations($user);
		$em->flush();
	}
}
