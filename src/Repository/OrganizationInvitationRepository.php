<?php

namespace App\Repository;

use App\Entity\OrganizationInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganizationInvitation>
 */
class OrganizationInvitationRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, OrganizationInvitation::class);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return OrganizationInvitation|null
	 */
	public function find($id, $lockMode = null, $lockVersion = null)
	{
		return parent::find($id, $lockMode, $lockVersion);
	}
}
