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
 */
class RecommendationRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Recommendation::class);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return Recommendation|null
	 */
	public function find($id, $lockMode = null, $lockVersion = null)
	{
		return parent::find($id, $lockMode, $lockVersion);
	}

	/**
	 * Finds every recommendation matching a tool response's URL and tool name.
	 *
	 * @return ArrayCollection<int,Recommendation>
	 */
	public function findFromToolResponse(ToolResponse $toolResponse): ArrayCollection
	{
		$result = $this->createQueryBuilder('r')
			->innerJoin('r.relatedPage', 'relatedPage')
			->innerJoin('r.parentResult', 'parentResult')
			->innerJoin('parentResult.parentResponse', 'parentResponse')
			->innerJoin('r.relatedPage', 'p')
			->andWhere('relatedPage.url = :url')
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
