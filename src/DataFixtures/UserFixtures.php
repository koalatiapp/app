<?php

namespace App\DataFixtures;

use App\Entity\User;
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
		$user = new User();
		$user->setEmail('name@email.com');
		$user->setFirstName('Test');
		$user->setLastName('User');
		$user->setPassword($this->passwordHasher->hashPassword(
			$user,
			'123456'
		));
		$manager->persist($user);
		$manager->flush();
	}
}
