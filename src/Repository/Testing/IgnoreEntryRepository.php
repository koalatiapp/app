<?php

namespace App\Repository\Testing;

use App\Entity\Testing\IgnoreEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IgnoreEntry>
 *
 * @method IgnoreEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method IgnoreEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method IgnoreEntry[]    findAll()
 * @method IgnoreEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IgnoreEntryRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, IgnoreEntry::class);
	}
}
