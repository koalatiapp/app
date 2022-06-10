<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\ProjectActivityRecord;
use App\Entity\User;
use DateTime;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectActivityRecord>
 */
class ProjectActivityRecordRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ProjectActivityRecord::class);
	}

	public function getActiveProjectCount(User|Organization $entity, DateTimeInterface|string|null $fromDate = null, DateTimeInterface|string|null $toDate = null): int
	{
		if (!$fromDate) {
			$fromDate = new DateTime('first day of this month midnight');
		} elseif (is_string($fromDate)) {
			$fromDate = new DateTime($fromDate);
		}

		if (!$toDate) {
			$toDate = new DateTime('first day of next month midnight');
		} elseif (is_string($toDate)) {
			$toDate = new DateTime($toDate);
		}

		$em = $this->getEntityManager();
		$user = $entity instanceof Organization ? $entity->getOwner() : $entity;
		$parameters = [
			'user' => $user,
			'fromDate' => $fromDate,
			'toDate' => $toDate,
		];

		/*
		 * Protocols, www subdomain and trailing slashes are not taken into account
		 * when comparing website URLs to give the users some leeway in case they
		 * mistakenly set up the wrong URL in the project, to prevent negative
		 * feedback if they secure their website, etc.
		  */
		$query = $em->createQuery("
				SELECT COUNT(
					DISTINCT(
						TRIM(TRAILING '/' FROM
							TRIM(LEADING 'www.' FROM
								TRIM(LEADING 'http://' FROM
									TRIM(LEADING 'https://' FROM r.websiteUrl)
								)
							)
						)
					)
				)
				FROM App\Entity\ProjectActivityRecord r
				WHERE r.user = :user
				AND r.dateCreated >= :fromDate
				AND r.dateCreated < :toDate
			")->setParameters($parameters);

		return (int) $query->getSingleScalarResult();
	}
}
