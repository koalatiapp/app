<?php

namespace App\Controller\Project;

use App\Controller\Trait\SuggestUpgradeControllerTrait;
use App\Message\TestingRequest;
use App\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectTestingController extends AbstractProjectController
{
	use SuggestUpgradeControllerTrait;

	/**
	 * @Route("/project/{id}/testing", name="project_testing")
	 */
	public function projectTesting(int $id): Response
	{
		$project = $this->getProject($id);

		if (!$this->isGranted(ProjectVoter::TESTING, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.testing');
		}

		if (!$project->getRecommendations()->count()) {
			$this->dispatchMessage(new TestingRequest($id));
		}

		return $this->render('app/project/testing/index.html.twig', [
			'project' => $project,
		]);
	}
}
