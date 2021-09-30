<?php

namespace App\Controller\Api\Testing;

use App\Controller\Api\AbstractApiController;
use App\Mercure\TopicBuilder;
use App\Message\TestingRequest;
use App\Repository\Testing\RecommendationRepository;
use App\Security\ProjectVoter;
use App\Util\Testing\RecommendationGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/testing/recommendations", name="api_testing_recommendation_")
 */
class RecommendationController extends AbstractApiController
{
	/**
	 * Returns the list of recommendations for the project, grouped by type.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 *
	 * @Route("/groups", methods={"GET","HEAD"}, name="group_list", options={"expose": true})
	 */
	public function listGroups(Request $request): JsonResponse
	{
		$projectId = $request->query->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);
		$recommendationGroups = $project->getActiveRecommendationGroups();

		$this->setSuggestedMercureTopic($this->topicBuilder->getEntityGenericTopic(RecommendationGroup::class, TopicBuilder::SCOPE_PROJECT, $projectId));

		return $this->apiSuccess($recommendationGroups);
	}

	/**
	 * Return the detailed record of a recommendation group.
	 *
	 * @Route("/groups/{id}", methods={"GET","HEAD"}, name="group_details", options={"expose": true})
	 */
	public function detailsGroup(int $id, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$recommendation = $recommendationRepository->find($id);

		if (!$recommendation) {
			return $this->notFound();
		}

		$project = $recommendation->getProject();

		if (!$this->isGranted(ProjectVoter::VIEW, $project)) {
			return $this->accessDenied();
		}

		$recommendationGroups = $project->getActiveRecommendationGroups();
		$recommendationGroup = $recommendationGroups[$recommendation->getUniqueName()];

		// In some cases, the recommendation group doesn't exist anymore.
		// Ex.: it's been marked as completed and there's been a race condition.
		// If that's the case, build a new one with just this recommendation.
		if (!$recommendationGroup) {
			$recommendationGroup = new RecommendationGroup(new ArrayCollection([$recommendation]));
		}

		$this->setSuggestedMercureTopic($this->topicBuilder->getEntityTopic($recommendationGroup, TopicBuilder::SCOPE_SPECIFIC));

		return $this->apiSuccess($recommendationGroup, ['recommendation_group', 'recommendation']);
	}

	/**
	 * Marks all recommendations from a given group as completed.
	 *
	 * @Route("/groups/{id}/complete", methods={"PUT"}, name="group_complete", options={"expose": true})
	 */
	public function completeGroup(int $id, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$recommendation = $recommendationRepository->find($id);

		if (!$recommendation) {
			return $this->notFound();
		}

		$project = $recommendation->getProject();

		if (!$this->isGranted(ProjectVoter::PARTICIPATE, $project)) {
			return $this->accessDenied();
		}

		$em = $this->getDoctrine()->getManager();
		$recommendationGroups = $project->getActiveRecommendationGroups();
		$recommendationGroup = $recommendationGroups[$recommendation->getUniqueName()];

		foreach ($recommendationGroup->getRecommendations() as $recommendation) {
			$recommendation->complete($this->getUser());
			$em->persist($recommendation);
		}

		$em->flush();

		// Trigger a new testing request for this tool
		$testingRequest = new TestingRequest(
			$project->getId(),
			$recommendationGroup->getSample()->getParentResult()->getParentResponse()->getTool()
		);
		$this->dispatchMessage($testingRequest);

		return $this->apiSuccess($recommendationGroup);
	}

	/**
	 * Returns the list of recommendations for the project.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 * - `show_completed` - `bool` (defaults to `false`) - Whether completed recommendations should be included
	 *
	 * @Route("", methods={"GET","HEAD"}, name="list", options={"expose": true})
	 */
	public function list(Request $request): JsonResponse
	{
		$projectId = $request->query->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);

		if ($request->query->get('show_completed')) {
			return $this->apiSuccess($project->getSortedRecommendations());
		}

		return $this->apiSuccess($project->getActiveRecommendations());
	}

	/**
	 * Returns the detailed record of a single recommmendation.
	 *
	 * @Route("/{id}", methods={"GET","HEAD"}, name="details", options={"expose": true})
	 */
	public function details(int $id, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$recommendation = $recommendationRepository->find($id);

		if (!$recommendation) {
			return $this->apiError('This recommmendation does not exist.', 404);
		}

		$project = $recommendation->getProject();

		if (!$this->isGranted(ProjectVoter::VIEW, $project)) {
			return $this->accessDenied();
		}

		return $this->apiSuccess($recommendation, ['recommendation']);
	}
}
