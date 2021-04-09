<?php

namespace App\Repository\Testing;

use App\Entity\Testing\Recommendation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Recommendation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recommendation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recommendation[]    findAll()
 * @method Recommendation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecommendationRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Recommendation::class);
	}

	// /**
	//  * @return Recommendation[] Returns an array of Recommendation objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('r')
			->andWhere('r.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('r.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Recommendation
	{
		return $this->createQueryBuilder('r')
			->andWhere('r.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
