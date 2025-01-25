<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\OrganizationMember;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Comment::class);
	}

	/**
	 * Finds all unresolved threads for a given project.
	 *
	 * @return array<int,Comment>
	 */
	public function findUnresolvedThreadsByProject(Project $project): array
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.thread IS NULL')
			->andWhere('c.isResolved = 0')
			->andWhere('c.project = :project')
			->setParameter('project', $project)
			->orderBy('c.dateCreated', 'ASC')
			->getQuery()
			->getResult()
		;
	}

	/**
	 * Finds comments from a user's search query.
	 *
	 * @param array<string> $queryParts
	 *
	 * @return array<Comment>
	 */
	public function findBySearchQuery(array $queryParts, ?User $requestingUser = null)
	{
		if (!$queryParts) {
			return [];
		}

		$queryBuilder = $this->createQueryBuilder('c')
			->join('c.project', 'p')
			->andWhere('c.textContent NOT LIKE :emptyString')
			->setParameter("emptyString", "")
			->addOrderBy('c.isResolved', 'DESC')
			->addOrderBy('c.dateCreated', 'DESC');

		if ($requestingUser) {
			// Add project accessibility check (looks for direct ownership or shared team project)
			$accessibleOrganizations = $requestingUser->getOrganizationLinks()->map(fn (?OrganizationMember $link = null) => $link->getOrganization());

			$userMatchExpression = $queryBuilder->expr()->orX();
			$userMatchExpression->add($queryBuilder->expr()->eq('p.ownerUser', ':user'));
			$userMatchExpression->add($queryBuilder->expr()->in('p.ownerOrganization', ':organizations'));

			$queryBuilder->andWhere($userMatchExpression)
				->setParameter('user', $requestingUser)
				->setParameter('organizations', $accessibleOrganizations);
		}

		foreach ($queryParts as $index => $part) {
			$queryBuilder->andWhere('c.textContent LIKE :queryPart'.$index)
				->setParameter('queryPart'.$index, '%'.addcslashes($part, '%_').'%');
		}

		return $queryBuilder->getQuery()->getResult();
	}
}
