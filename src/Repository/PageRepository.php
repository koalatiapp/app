<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Page::class);
	}

	/**
	 * Finds pages corresponding to a provided list of URLs.
	 *
	 * @param string[] $urls
	 *
	 * @return Page[]
	 */
	public function findByUrls(array $urls)
	{
		$urls = array_values($urls);

		return $this->createQueryBuilder('p')
			->where('p.url IN (:urls)')
			->setParameter('urls', $urls)
			->getQuery()
			->getResult();
	}

	/**
	 * Finds a page corresponding to a provided URL.
	 */
	public function findOneByUrl(string $url): ?Page
	{
		return $this->createQueryBuilder('p')
			->where('p.url = :url')
			->setParameter('url', $url)
			->getQuery()
			->getOneOrNullResult();
	}
}
