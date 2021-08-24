<?php

namespace App\EventListener;

use App\Entity\Organization;
use App\Util\DeletionManager\OrganizationDeletionManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class OrganizationDeletionListener
{
	public function preRemove(Organization $organization, LifecycleEventArgs $event): void
	{
		$organizationDeletionManager = new OrganizationDeletionManager($event->getEntityManager());
		$organizationDeletionManager->deleteRelations($organization);
	}
}
