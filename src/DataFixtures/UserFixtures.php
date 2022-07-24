<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Subscription\Plan\BusinessPlan;
use App\Subscription\Plan\NoPlan;
use App\Subscription\Plan\SmallTeamPlan;
use App\Subscription\Plan\SoloPlan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
	/**
	 * @var UserPasswordHasherInterface
	 */
	private $passwordHasher;

	public function __construct(UserPasswordHasherInterface $passwordHasher)
	{
		$this->passwordHasher = $passwordHasher;
	}

	public function load(ObjectManager $manager): void
	{
		// Test user
		$user = new User();
		$user->setEmail('name@email.com')
			->setFirstName('Test')
			->setLastName('User')
			->setSubscriptionPlan(SmallTeamPlan::UNIQUE_NAME)
			->setPassword($this->passwordHasher->hashPassword(
				$user,
				'123456'
			));
		$manager->persist($user);

		// User without subscription plan
		$user = new User();
		$user->setEmail('no@plan.com')
			->setFirstName('Jimmy "Free"')
			->setLastName('Will')
			->setSubscriptionPlan(NoPlan::UNIQUE_NAME)
			->setPassword($this->passwordHasher->hashPassword(
				$user,
				'123456'
			));
		$manager->persist($user);

		// User with "Solo" plan
		$user = new User();
		$user->setEmail('solo@plan.com')
			->setFirstName('Solomon "Solo"')
			->setLastName('Jackson')
			->setSubscriptionPlan(SoloPlan::UNIQUE_NAME)
			->setPassword($this->passwordHasher->hashPassword(
				$user,
				'123456'
			));
		$manager->persist($user);

		// User with "SmallTeam" plan
		$user = new User();
		$user->setEmail('smallteam@plan.com')
			->setFirstName('Stan "Small Team"')
			->setLastName('Thomas')
			->setSubscriptionPlan(SmallTeamPlan::UNIQUE_NAME)
			->setPassword($this->passwordHasher->hashPassword(
				$user,
				'123456'
			));
		$manager->persist($user);

		// User with "Solo" plan
		$user = new User();
		$user->setEmail('business@plan.com')
			->setFirstName('Boris "The Business"')
			->setLastName('Morrison')
			->setSubscriptionPlan(BusinessPlan::UNIQUE_NAME)
			->setPassword($this->passwordHasher->hashPassword(
				$user,
				'123456'
			));
		$manager->persist($user);

		$manager->flush();
	}
}
