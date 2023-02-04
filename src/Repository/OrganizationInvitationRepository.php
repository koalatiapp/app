<?php

namespace App\Repository;

use App\Entity\OrganizationInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganizationInvitation>
 *
 * @method OrganizationInvitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganizationInvitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganizationInvitation[]    findAll()
 * @method OrganizationInvitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationInvitationRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, OrganizationInvitation::class);
	}
}
