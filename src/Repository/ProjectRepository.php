<?php

namespace App\Repository;

use App\Entity\OrganizationMember;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Project::class);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return Project|null
	 */
	public function find($id, $lockMode = null, $lockVersion = null)
	{
		return parent::find($id, $lockMode, $lockVersion);
	}

	/**
	 * Finds projects from a user's search query.
	 *
	 * @param array<string> $queryParts
	 *
	 * @return array<Project>
	 */
	public function findBySearchQuery(array $queryParts, ?User $requestingUser = null)
	{
		if (!$queryParts) {
			return [];
		}

		$queryBuilder = $this->createQueryBuilder('p')
			->orderBy('p.dateCreated', 'DESC');

		if ($requestingUser) {
			// Add project accessibility check (looks for direct ownership or shared team project)
			$accessibleOrganizations = $requestingUser->getOrganizationLinks()->map(fn (OrganizationMember $link) => $link->getOrganization());

			$userMatchExpression = $queryBuilder->expr()->orX();
			$userMatchExpression->add($queryBuilder->expr()->eq('p.ownerUser', ':user'));
			$userMatchExpression->add($queryBuilder->expr()->in('p.ownerOrganization', ':organizations'));

			$queryBuilder->andWhere($userMatchExpression)
				->setParameter('user', $requestingUser)
				->setParameter('organizations', $accessibleOrganizations);
		}

		foreach ($queryParts as $index => $part) {
			$queryBuilder->andWhere('p.name LIKE :namePart'.$index)
				->setParameter('namePart'.$index, '%'.addcslashes($part, '%_').'%');
		}

		return $queryBuilder->getQuery()->getResult();
	}

	/**
	 * Finds projects corresponding to a provided URL.
	 *
	 * @return array<int,Project>
	 */
	public function findByUrl(string $url): array
	{
		return $this->createQueryBuilder('p')
			->where('p.url = :url')
			->setParameter('url', $url)
			->getQuery()
			->getResult();
	}

	/**
	 * Finds projects corresponding to a provided page URL.
	 *
	 * @return array<int,Project>
	 */
	public function findByPageUrl(string $pageUrl): array
	{
		$em = $this->getEntityManager();

		/*
		 * Protocols, www subdomain and trailing slashes are not taken into account
		 * when comparing website URLs to give the users some leeway in case they
		 * mistakenly set up the wrong URL in the project, to prevent negative
		 * feedback if they secure their website, etc.
		  */
		$query = $em->createQuery("
				SELECT p
				FROM App\Entity\Project p
				WHERE TRIM(TRAILING '/' FROM
					TRIM(LEADING 'www.' FROM
						TRIM(LEADING 'http://' FROM
							TRIM(LEADING 'https://' FROM :pageUrl)
						)
					)
				) LIKE CONCAT(
					TRIM(TRAILING '/' FROM
						TRIM(LEADING 'www.' FROM
							TRIM(LEADING 'http://' FROM
								TRIM(LEADING 'https://' FROM p.url)
							)
						)
					),
					'%'
				)
		")->setParameter('pageUrl', $pageUrl);

		return $query->getResult();
	}
}
