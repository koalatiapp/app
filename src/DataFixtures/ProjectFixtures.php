<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\Page;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\Framework;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectFixtures extends Fixture implements DependentFixtureInterface
{
	public function __construct(
		private readonly UserRepository $userRepository,
		private readonly OrganizationRepository $organizationRepository,
	) {
	}

	public function load(ObjectManager $manager): void
	{
		$users = $this->userRepository->findAll();

		foreach ($users as $user) {
			$this->createProjectFixture($manager, $user);
		}

		foreach ($this->organizationRepository->findAll() as $organization) {
			$this->createProjectFixture($manager, $organization);
		}

		$manager->flush();
	}

	private function createProjectFixture(ObjectManager $manager, User|Organization $owner): Project
	{
		$project = new Project();
		$project->setName('Koalati');
		$project->setUrl('https://koalati.com');
		$project->setTags([Framework::WEBFLOW]);

		if ($owner instanceof User) {
			$project->setOwnerUser($owner);
		} else {
			$project->setOwnerOrganization($owner);
		}

		$homePage = new Page($project, 'https://koalati.com', 'Homepage - Koalati');
		$project->addPage($homePage);
		$manager->persist($homePage);

		$aboutPage = new Page($project, 'https://koalati.com/about', 'About - Koalati');
		$project->addPage($aboutPage);
		$manager->persist($aboutPage);

		$manager->persist($project);

		return $project;
	}

	public function getDependencies()
	{
		return [
			UserFixtures::class,
			OrganizationFixtures::class,
		];
	}
}
