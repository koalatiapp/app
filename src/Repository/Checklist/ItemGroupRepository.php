<?php

namespace App\Repository\Checklist;

use App\Entity\Checklist\ItemGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ItemGroup>
 *
 * @method ItemGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemGroup[]    findAll()
 * @method ItemGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemGroupRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ItemGroup::class);
	}
}
