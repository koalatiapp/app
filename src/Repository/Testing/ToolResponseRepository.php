<?php

namespace App\Repository\Testing;

use App\Entity\Testing\ToolResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ToolResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method ToolResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method ToolResponse[]    findAll()
 * @method ToolResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ToolResponseRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ToolResponse::class);
	}

	// /**
	//  * @return ToolResponse[] Returns an array of ToolResponse objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('t')
			->andWhere('t.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('t.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?ToolResponse
	{
		return $this->createQueryBuilder('t')
			->andWhere('t.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
