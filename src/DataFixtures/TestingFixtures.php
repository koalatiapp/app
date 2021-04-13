<?php

namespace App\DataFixtures;

use App\Entity\Testing\Recommendation;
use App\Repository\ProjectRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestingFixtures extends Fixture implements DependentFixtureInterface
{
	/**
	 * @var ProjectRepository
	 */
	private $projectRepository;

	public function __construct(ProjectRepository $projectRepository)
	{
		$this->projectRepository = $projectRepository;
	}

	public function load(ObjectManager $manager): void
	{
		$projects = $this->projectRepository->findAll();

		foreach ($projects as $project) {
			foreach ($project->getActivePages() as $page) {
				// Generate recommendations
				$recommendation = new Recommendation();
				$recommendation->setType(Recommendation::TYPE_ISSUE)
					->setTemplate('Add an alt attribute to all of your <img> tags to describe their content.')
					->setProject($project)
					->setRelatedPage($page);
				$manager->persist($recommendation);
			}
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
