<?php

namespace App\Controller\Project;

use App\Message\TestingRequest;
use App\Subscription\QuotaManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectTestingController extends AbstractProjectController
{
	/**
	 * @Route("/project/{id}/testing", name="project_testing")
	 */
	public function projectTesting(int $id, QuotaManager $quotaManager): Response
	{
		$project = $this->getProject($id);

		if (!$project->getRecommendations()->count()) {
			$this->dispatchMessage(new TestingRequest($id));
			$quotaManager->notifyIfQuotaExceeded($project);
		}

		return $this->render('app/project/testing/index.html.twig', [
			'project' => $project,
		]);
	}
}
