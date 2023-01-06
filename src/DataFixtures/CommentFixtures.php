<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Organization;
use App\Repository\Checklist\ChecklistRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
	public function __construct(
		private readonly ChecklistRepository $checklistRepository,
	) {
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->checklistRepository->findAll() as $checklist) {
			$projectOwner = $checklist->getProject()->getOwner();

			if ($projectOwner instanceof Organization) {
				$projectOwner = $projectOwner->getOwner();
			}

			$firstItem = $checklist->getItems()->first();
			$comment = (new Comment())
				->setAuthor($projectOwner)
				->setChecklistItem($firstItem)
				->setContent("<p>I think we need to look into fixing this</p>")
				->setProject($checklist->getProject())
			;
			$manager->persist($comment);

			$reply = (new Comment())
				->setAuthor($projectOwner)
				->setChecklistItem($firstItem)
				->setContent("<p>Does anyone agree?</p>")
				->setProject($checklist->getProject())
				->setThread($comment);

			$manager->persist($reply);
		}

		$manager->flush();
	}

	public function getDependencies()
	{
		return [
			ProjectFixtures::class,
		];
	}
}
