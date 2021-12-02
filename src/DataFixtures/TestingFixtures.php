<?php

namespace App\DataFixtures;

use App\Entity\Testing\IgnoreEntry;
use App\Entity\Testing\Recommendation;
use App\Entity\Testing\TestResult;
use App\Entity\Testing\ToolResponse;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TestingFixtures extends Fixture implements DependentFixtureInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private UserRepository $userRepository,
	) {
	}

	public function load(ObjectManager $manager): void
	{
		$user = $this->userRepository->findOneByEmail('name@email.com');
		$project = $user->getAllProjects()->first();

		foreach ($project->getActivePages() as $page) {
			$toolResponse = (new ToolResponse())
				->setTool('@koalati/tool-accessibility')
				->setUrl($page->getUrl())
				->setProcessingTime(1000);
			$manager->persist($toolResponse);

			// Create a test and a recommendation that will be visible to the user
			$altDescriptionTestResult = (new TestResult())
				->setTitle('Alternative image descriptions')
				->setUniqueName('images_alt_description')
				->setDescription('Alt text is a tenet of accessible web design. Its original (and still primary) purpose is to describe images to visitors who are unable to see them. This includes screen readers and browsers that block images, but it also includes users who are sight-impaired or otherwise unable to visually identify an image.')
				->setWeight(1)
				->setScore(0.5)
				->setParentResponse($toolResponse);
			$manager->persist($altDescriptionTestResult);

			$altDescriptionRecommendation = (new Recommendation())
				->setType(Recommendation::TYPE_ISSUE)
				->setUniqueName('images_alt_description_missing')
				->setTemplate('Add an alt attribute to all of your `<img>` tags to describe their content.')
				->setParentResult($altDescriptionTestResult)
				->setRelatedPage($page);
			$manager->persist($altDescriptionRecommendation);

			// Create a test and a recommendation that is ignored for this project
			$colorContrastTestResult = (new TestResult())
				->setTitle('Color contrast of text')
				->setUniqueName('color_contrast')
				->setDescription('High contrasts between a piece of text and its background ensures that the content is readable.')
				->setWeight(1)
				->setScore(0.95)
				->setParentResponse($toolResponse);
			$manager->persist($colorContrastTestResult);

			$colorContrastRecommendation = (new Recommendation())
				->setType(Recommendation::TYPE_ESSENTIAL)
				->setUniqueName('low_color_contrast')
				->setTemplate('Increase the contrast of your text relative to its background to improve readability for your users.')
				->setParentResult($colorContrastTestResult)
				->setRelatedPage($page);
			$manager->persist($colorContrastRecommendation);

			$ignoreEntry = new IgnoreEntry(
				'@koalati/tool-accessibility',
				'color_contrast',
				'low_color_contrast',
				'Increase the contrast of your text relative to its background to improve readability for your users.',
				$project,
				$user
			);
			$manager->persist($ignoreEntry);
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
