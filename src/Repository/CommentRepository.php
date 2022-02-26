<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Project;
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
}
