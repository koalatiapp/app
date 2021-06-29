<?php

namespace App\Repository\Testing;

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

	/**
	 * {@inheritDoc}
	 *
	 * @return IgnoreEntry|null
	 */
	public function find($id, $lockMode = null, $lockVersion = null)
	{
		return parent::find($id, $lockMode, $lockVersion);
	}
}
