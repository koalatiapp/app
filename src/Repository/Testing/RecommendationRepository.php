<?php

namespace App\Repository\Testing;

use App\Entity\Testing\Recommendation;
use App\Entity\Testing\ToolResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for automated testing recommendations.
 *
 * @extends ServiceEntityRepository<Recommendation>
 *
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

	/**
	 * Finds every recommendation matching a tool response's URL and tool name.
	 *
	 * @return ArrayCollection<int,Recommendation>
	 */
	public function findFromToolResponse(ToolResponse $toolResponse): ArrayCollection
	{
		$result = $this->createQueryBuilder('r')
			->innerJoin('r.parentResult', 'parentResult')
			->innerJoin('parentResult.parentResponse', 'parentResponse')
			->andWhere('parentResponse.url = :url')
			->andWhere('parentResponse.tool = :tool')
			->setParameter('url', $toolResponse->getUrl())
			->setParameter('tool', $toolResponse->getTool())
			->orderBy('r.dateLastOccured', 'DESC')
			->getQuery()
			->getResult()
		;

		return new ArrayCollection($result);
	}
}
