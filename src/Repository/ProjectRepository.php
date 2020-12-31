<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Project::class);
	}

	/**
	 * Finds projects from a user's search query.
	 *
	 * @param array<string> $queryParts
	 *
	 * @return array<Project>
	 */
	public function findBySearchQuery(array $queryParts, User $requestingUser)
	{
		if (!$queryParts) {
			return [];
		}

		$qb = $this->createQueryBuilder('p')
			->andWhere('p.ownerUser = :user')
			->setParameter('user', $requestingUser)
			->orderBy('p.dateCreated', 'DESC');

		foreach ($queryParts as $index => $part) {
			$qb->andWhere('p.name LIKE :namePart'.$index)
				->setParameter('namePart'.$index, '%'.addcslashes($part, '%_').'%');
		}

		return $qb->getQuery()->getResult();
	}

	public function findById(int $id, User $requestingUser): ?Project
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.id = :id')
			->andWhere('p.ownerUser = :user')
			->setParameter('id', $id)
			->setParameter('user', $requestingUser)
			->getQuery()
			->getOneOrNullResult()
		;
	}
}
