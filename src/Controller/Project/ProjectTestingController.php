<?php

namespace App\Controller\Project;

use App\Controller\Trait\SuggestUpgradeControllerTrait;
use App\Message\TestingRequest;
use App\Security\ProjectVoter;
use App\Subscription\UsageManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProjectTestingController extends AbstractProjectController
{
	use SuggestUpgradeControllerTrait;

	#[Route(path: '/project/{id}/testing', name: 'project_testing')]
	public function projectTesting(int $id, MessageBusInterface $bus, UsageManager $usageManager): Response
	{
		$project = $this->getProject($id);

		if (!$this->isGranted(ProjectVoter::TESTING, $project)) {
			return $this->suggestPlanUpgrade('upgrade_suggestion.testing');
		}

		if (!$project->getActiveRecommendations()->count()) {
			$bus->dispatch(new TestingRequest($id));
		}

		return $this->render('app/project/testing/index.html.twig', [
			'project' => $project,
			'usageManager' => $usageManager->withUser($project->getTopLevelOwner()),
		]);
	}
}
