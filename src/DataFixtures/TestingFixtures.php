<?php

namespace App\DataFixtures;

use App\Entity\Testing\Recommendation;
use App\Entity\Testing\TestResult;
use App\Entity\Testing\ToolResponse;
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
				$toolResponse = (new ToolResponse())
					->setTool('@koalati/tool-accessibility')
					->setUrl($page->getUrl())
					->setProcessingTime(1000);
				$manager->persist($toolResponse);

				// Generate test results
				$result = (new TestResult())
					->setTitle('Alternative image descriptions')
					->setUniqueName('images_alt_description')
					->setDescription('Alt text is a tenet of accessible web design. Its original (and still primary) purpose is to describe images to visitors who are unable to see them. This includes screen readers and browsers that block images, but it also includes users who are sight-impaired or otherwise unable to visually identify an image.')
					->setWeight(1)
					->setScore(0.5)
					->setParentResponse($toolResponse);
				$manager->persist($result);

				// Generate recommendations
				$recommendation = new Recommendation();
				$recommendation->setType(Recommendation::TYPE_ISSUE)
					->setUniqueName('images_alt_description_missing')
					->setTemplate('Add an alt attribute to all of your `<img>` tags to describe their content.')
					->setType(Recommendation::TYPE_ISSUE)
					->setParentResult($result)
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
