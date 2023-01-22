<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\ProjectActivityRecord;
use App\Entity\User;
use App\Subscription\UsageManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends ServiceEntityRepository<ProjectActivityRecord>
 *
 * @method ProjectActivityRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectActivityRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectActivityRecord[]    findAll()
 * @method ProjectActivityRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectActivityRecordRepository extends ServiceEntityRepository
{
	private UsageManager $usageManager;

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ProjectActivityRecord::class);
	}

	#[Required]
	public function setDependencies(UsageManager $usageManager): void
	{
		$this->usageManager = $usageManager;
	}

	/**
	 * @return array<int,ProjectActivityRecord>
	 */
	public function findAllForUser(User $user): array
	{
		return $this->findBy(["user" => $user], ["dateCreated" => "DESC", "pageUrl" => "ASC", "tool" => "ASC"]);
	}

	/**
	 * @return array<int,ProjectActivityRecord>
	 */
	public function findAllForUserInCycle(User $user, \DateTimeInterface|string $cycleStartDate): array
	{
		if (is_string($cycleStartDate)) {
			$cycleStartDate = new \DateTime($cycleStartDate);
		}

		return $this->createQueryBuilder("r")
			->where("r.user = :user")
			->andWhere("r.dateCreated >= :cycleStartDate")
			->andWhere("r.dateCreated <= :cycleEndDate")
			->addOrderBy("r.dateCreated", "DESC")
			->addOrderBy("r.pageUrl", "ASC")
			->addOrderBy("r.tool", "ASC")
			->setParameters([
				"user" => $user,
				"cycleStartDate" => $cycleStartDate,
				"cycleEndDate" => $this->usageManager->getUsageCycleEndDate($cycleStartDate),
			])
			->getQuery()
			->getResult();
	}

	public function getActiveProjectCount(User|Organization $entity, \DateTimeInterface|string|null $fromDate = null, \DateTimeInterface|string|null $toDate = null): int
	{
		if (!$fromDate) {
			$fromDate = new \DateTime('first day of this month midnight');
		} elseif (is_string($fromDate)) {
			$fromDate = new \DateTime($fromDate);
		}

		if (!$toDate) {
			$toDate = new \DateTime('first day of next month midnight');
		} elseif (is_string($toDate)) {
			$toDate = new \DateTime($toDate);
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
