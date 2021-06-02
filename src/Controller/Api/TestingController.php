<?php

namespace App\Controller\Api;

use App\Message\TestingRequest;
use App\Repository\Testing\RecommendationRepository;
use App\Security\ProjectVoter;
use App\Trait\ProjectControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/testing", name="api_testing_")
 */
class TestingController extends AbstractApiController
{
	use ProjectControllerTrait;

	/**
	 * Submits an automated testing request for the project.
	 * Testing will be requested for every page and for every tool.
	 *
	 * @Route("/request/{projectId}", methods={"POST"}, name="request", options={"expose": true})
	 */
	public function testingRequest(int $projectId): JsonResponse
	{
		$project = $this->getProject($projectId);

		if (!$this->isGranted(ProjectVoter::PARTICIPATE, $project)) {
			return $this->accessDenied();
		}

		$this->dispatchMessage(new TestingRequest($project->getId()));

		return $this->apiSuccess();
	}

	/**
	 * Return a list of grouped recommendations for a project.
	 *
	 * @Route("/recommendation/groups/{projectId}", methods={"GET","HEAD"}, name="recommendation_groups", options={"expose": true})
	 */
	public function recommendationGroups(int $projectId): JsonResponse
	{
		$project = $this->getProject($projectId);

		if (!$this->isGranted(ProjectVoter::VIEW, $project)) {
			return $this->accessDenied();
		}

		$recommendationGroups = $project->getActiveRecommendationGroups();

		return $this->apiSuccess($recommendationGroups);
	}

	/**
	 * Return the recommendation group for a given recommendation.
	 *
	 * @Route("/recommendation/group/{recommendationId}", methods={"GET","HEAD"}, name="recommendation_group", options={"expose": true})
	 */
	public function recommendationGroup(int $recommendationId, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$recommendation = $recommendationRepository->find($recommendationId);

		if (!$recommendation) {
			return $this->notFound();
		}

		$project = $recommendation->getProject();
		$recommendationGroups = $project->getActiveRecommendationGroups();
		$recommendationGroup = $recommendationGroups[$recommendation->getUniqueName()];

		if (!$this->isGranted(ProjectVoter::VIEW, $project)) {
			return $this->accessDenied();
		}

		return $this->apiSuccess($recommendationGroup, ['recommendation_group', 'recommendation']);
	}

	/**
	 * Returns the detailed record of a single recommmendation.
	 *
	 * @Route("/recommendation/{recommendationId}", methods={"GET","HEAD"}, name="recommendation", options={"expose": true})
	 */
	public function recommendation(int $recommendationId, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$recommendation = $recommendationRepository->find($recommendationId);

		if (!$recommendation) {
			return $this->apiError('This recommmendation does not exist.', 404);
		}

		$project = $recommendation->getProject();

		if (!$this->isGranted(ProjectVoter::VIEW, $project)) {
			return $this->accessDenied();
		}

		return $this->apiSuccess($recommendation, ['recommendation']);
	}

	/**
	 * Marks all recommendations from a given group as completed.
	 *
	 * @Route("/recommendation/group/{recommendationId}/complete", methods={"PUT"}, name="recommendation_group_complete", options={"expose": true})
	 */
	public function markRecommendationGroupAsCompleted(int $recommendationId, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();
		$recommendation = $recommendationRepository->find($recommendationId);

		if (!$recommendation) {
			return $this->notFound();
		}

		$project = $recommendation->getProject();

		if (!$this->isGranted(ProjectVoter::PARTICIPATE, $project)) {
			return $this->accessDenied();
		}

		$recommendationGroups = $project->getActiveRecommendationGroups();
		$recommendationGroup = $recommendationGroups[$recommendation->getUniqueName()];

		foreach ($recommendationGroup->getRecommendations() as $recommendation) {
			$recommendation->complete($user);
			$em->persist($recommendation);
		}

		$em->flush();

		return $this->apiSuccess($recommendationGroup);
	}
}
