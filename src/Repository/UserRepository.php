<?php

namespace App\Repository;

use App\Entity\User;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\TrialPlan;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, User::class);
	}

	/**
	 * Used to upgrade (rehash) the user's password automatically over time.
	 */
	public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
	{
		if (!$user instanceof User) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
		}

		$user->setPassword($newEncodedPassword);
		$this->_em->persist($user);
		$this->_em->flush();
	}

	public function findOneByEmail(string $email): ?User
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.email = :email')
			->setParameter('email', $email)
			->getQuery()
			->getOneOrNullResult()
		;
	}

	public function findOneByPaddleUserId(string $paddleUserId): ?User
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.paddleUserId = :paddleUserId')
			->setParameter('paddleUserId', $paddleUserId)
			->getQuery()
			->getOneOrNullResult()
		;
	}

	/**
	 * @param string $soonDateModifier Datetime modifier string (ex.: `"+2 days"`) representing
	 *                                 the time from today from which a trial ending  is
	 *                                 considered "soon".
	 *
	 * @return array<int,User>
	 */
	public function findAllTrialsExpiringSoon(string $soonDateModifier): array
	{
		$expireBeforeDate = new DateTime($soonDateModifier);

		return $this->createQueryBuilder('u')
			->andWhere('u.subscriptionPlan = :trialPlan')
			->andWhere('u.upcomingSubscriptionPlan = :NoPlan')
			->andWhere('u.subscriptionChangeDate > :now')
			->andWhere('u.subscriptionChangeDate <= :expireBeforeDate')
			->setParameter('trialPlan', TrialPlan::UNIQUE_NAME)
			->setParameter('NoPlan', NoPlan::UNIQUE_NAME)
			->setParameter('now', new DateTime())
			->setParameter('expireBeforeDate', $expireBeforeDate)
			->getQuery()
			->getResult()
		;
	}
}
