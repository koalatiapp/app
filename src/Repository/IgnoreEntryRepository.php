<?php

namespace App\Repository;

use App\Entity\Testing\IgnoreEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IgnoreEntry>
 */
class IgnoreEntryRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, IgnoreEntry::class);
	}
}
