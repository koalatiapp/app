<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
	/**
	 * @var UserPasswordEncoderInterface
	 */
	private $passwordEncoder;

	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}

	public function load(ObjectManager $manager): void
	{
		$user = new User();
		$user->setEmail('name@email.com');
		$user->setFirstName('Test');
		$user->setLastName('User');
		$user->setPassword($this->passwordEncoder->encodePassword(
			$user,
			'123456'
		));
		$manager->persist($user);
		$manager->flush();
	}
}
