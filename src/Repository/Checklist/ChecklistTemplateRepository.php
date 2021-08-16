<?php

namespace App\Repository\Checklist;

use App\Entity\Checklist\ChecklistTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChecklistTemplate>
 *
 * @method ChecklistTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChecklistTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChecklistTemplate[]    findAll()
 * @method ChecklistTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChecklistTemplateRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ChecklistTemplate::class);
	}
}
