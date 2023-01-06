<?php

namespace App\Api\State;

use App\Entity\Project;
use App\Message\FaviconRequest;
use App\Message\ScreenshotRequest;
use App\Message\SitemapRequest;
use App\Security\ProjectVoter;
use App\Util\Checklist\Generator;
use App\Util\StackDetector;
use App\Util\Url;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends AbstractDoctrineStateWrapper<Project>
 */
class ProjectProcessor extends AbstractDoctrineStateWrapper
{
	public function __construct(
		private Url $urlHelper,
		private StackDetector $stackDetector,
		private Generator $checklistGenerator,
	) {
	}

	/**
	 * Hook before the persistence of a resource in the database.
	 *
	 * @param Project $project
	 */
	protected function prePersist(object &$project, ?array $originalData): void
	{
		if (!$project->getId() && !$project->getOwnerOrganization()) {
			$project->setOwnerUser($this->getUser());
		}

		if (($originalData['url'] ?? null) != $project->getUrl()) {
			$websiteUrl = $this->urlHelper->standardize($project->getUrl(), false);
			$project->setUrl($websiteUrl);

			// Detect website framework / CMS
			$framework = $this->stackDetector->detectFramework($websiteUrl);
			if ($framework) {
				$project->addTag($framework);
			}
		}

		if (!($originalData['owner_organization'] ?? null)) {
			if ($project->getOwnerOrganization()) {
				$project->setOwnerUser(null);
			} else {
				$project->setOwnerUser($this->getUser());
			}

			if (!$this->security->isGranted(ProjectVoter::EDIT, $project)) {
				throw new AccessDeniedException();
			}
		}
	}

	/**
	 * Hook after the persistence of a resource in the database.
	 *
	 * @param Project $project
	 */
	protected function postPersist(object &$project, ?array $originalData): void
	{
		// Generate the checklist if the project has just been created
		if (!$project->getChecklist()) {
			$checklist = $this->checklistGenerator->generateChecklist($project);
			$this->entityManager->persist($checklist);
			$this->entityManager->flush();
		}

		if (($originalData['url'] ?? null) != $project->getUrl()) {
			$this->bus->dispatch(new ScreenshotRequest($project->getId()));
			$this->bus->dispatch(new FaviconRequest($project->getId()));
			$this->bus->dispatch(new SitemapRequest($project->getId()));
		}
	}
}
