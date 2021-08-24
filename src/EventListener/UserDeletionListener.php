<?php

namespace App\EventListener;

use App\Entity\User;
use App\Util\DeletionManager\UserDeletionManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserDeletionListener
{
	public function preRemove(User $user, LifecycleEventArgs $event): void
	{
		$userDeletionManager = new UserDeletionManager($event->getEntityManager());
		$userDeletionManager->deleteRelations($user);
	}
}
