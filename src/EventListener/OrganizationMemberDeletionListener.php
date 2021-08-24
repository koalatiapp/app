<?php

namespace App\EventListener;

use App\Entity\OrganizationMember;
use App\Util\DeletionManager\OrganizationMemberDeletionManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class OrganizationMemberDeletionListener
{
	public function preRemove(OrganizationMember $member, LifecycleEventArgs $event): void
	{
		$memberDeletionManager = new OrganizationMemberDeletionManager($event->getEntityManager());
		$memberDeletionManager->deleteRelations($member);
	}
}
