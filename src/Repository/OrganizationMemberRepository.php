<?php

namespace App\Repository;

use App\Entity\OrganizationMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrganizationMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganizationMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganizationMember[]    findAll()
 * @method OrganizationMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationMemberRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, OrganizationMember::class);
	}

	// /**
	//  * @return OrganizationMember[] Returns an array of OrganizationMember objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('o')
			->andWhere('o.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('o.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?OrganizationMember
	{
		return $this->createQueryBuilder('o')
			->andWhere('o.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}