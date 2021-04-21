<?php

namespace App\Controller\Project;

use App\Repository\Testing\RecommendationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectTestingController extends AbstractProjectController
{
	/**
	 * @Route("/project/{id}/testing", name="project_testing")
	 */
	public function projectTesting(int $id): Response
	{
		$project = $this->getProject($id);

		return $this->render('app/project/testing/index.html.twig', [
			'project' => $project,
		]);
	}

	/**
	 * @Route("/recommendation-group/{recommendationId}", name="recommendation_group_modal", options={"expose": true})
	 */
	public function recommendationGroupModal(int $recommendationId, RecommendationRepository $recommendationRepository): Response
	{
		$recommendation = $recommendationRepository->find($recommendationId);
		$project = $recommendation->getProject();
		$recommendationGroups = $project->getActiveRecommendationGroups();
		$recommendationGroup = $recommendationGroups[$recommendation->getUniqueName()];

		return $this->render('app/project/testing/recommendation_details.html.twig', [
			'recommendationGroup' => $recommendationGroup,
		]);
	}
}
