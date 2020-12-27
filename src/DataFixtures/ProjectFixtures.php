<?php

namespace App\DataFixtures;

use App\Entity\Project;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
	/**
	 * @var UserRepository
	 */
	private $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	public function load(ObjectManager $manager): void
	{
		$users = $this->userRepository->findAll();

		foreach ($users as $user) {
			$project = new Project();
			$project->setName('Koalati');
			$project->setUrl('https://koalati.com');
			$project->setOwnerUser($user);
			$manager->persist($project);
		}

		$manager->flush();
	}

	public function getDependencies()
	{
		return [
			UserFixtures::class,
		];
	}
}
