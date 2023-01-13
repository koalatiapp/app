<?php

namespace App\Controller\Api\Testing;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Message\TestingRequest;
use App\Repository\Testing\RecommendationRepository;
use App\Security\ProjectVoter;
use App\Util\Testing\RecommendationGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/internal-api/testing/recommendations', name: 'api_testing_recommendation_')]
class RecommendationController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	/**
	 * Returns the list of recommendations for the project, grouped by type.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 */
	#[Route(path: '/groups', methods: ['GET', 'HEAD'], name: 'group_list', options: ['expose' => true])]
	public function listGroups(Request $request): JsonResponse
	{
		$projectId = $request->query->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);
		$recommendationGroups = $project->getActiveRecommendationGroups();

		return $this->apiSuccess($recommendationGroups);
	}

	/**
	 * Return the detailed record of a recommendation group.
	 */
	#[Route(path: '/groups/{id}', methods: ['GET', 'HEAD'], name: 'group_details', options: ['expose' => true])]
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

		return $this->apiSuccess($recommendationGroup, ['recommendation_group', 'recommendation']);
	}

	/**
	 * Marks all recommendations from a given group as completed.
	 */
	#[Route(path: '/groups/{id}/complete', methods: ['PUT'], name: 'group_complete', options: ['expose' => true])]
	public function completeGroup(int $id, RecommendationRepository $recommendationRepository, MessageBusInterface $bus): JsonResponse
	{
		$recommendation = $recommendationRepository->find($id);

		if (!$recommendation) {
			return $this->notFound();
		}

		$project = $recommendation->getProject();

		if (!$this->isGranted(ProjectVoter::PARTICIPATE, $project)) {
			return $this->accessDenied();
		}

		$recommendationGroups = $project->getActiveRecommendationGroups();
		$recommendationGroup = $recommendationGroups[$recommendation->getUniqueName()];

		// In some cases, the recommendation group doesn't exist anymore.
		// Ex.: it's been marked as completed and there's been a race condition.
		// If that's the case, build a new one with just this recommendation.
		if (!$recommendationGroup) {
			return $this->apiSuccess(new RecommendationGroup(new ArrayCollection([$recommendation])));
		}

		foreach ($recommendationGroup->getRecommendations() as $recommendation) {
			$recommendation->complete($this->getUser());
			$this->entityManager->persist($recommendation);
		}

		$this->entityManager->flush();

		// Trigger a new testing request for this tool
		$testingRequest = new TestingRequest(
			$project->getId(),
			[$recommendationGroup->getSample()->getParentResult()->getParentResponse()->getTool()],
		);
		$bus->dispatch($testingRequest);

		return $this->apiSuccess($recommendationGroup);
	}

	/**
	 * Returns the list of recommendations for the project.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 */
	#[Route(path: '', methods: ['GET', 'HEAD'], name: 'list', options: ['expose' => true])]
	public function list(Request $request): JsonResponse
	{
		$projectId = $request->query->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);

		return $this->apiSuccess($project->getActiveRecommendations());
	}

	/**
	 * Returns the detailed record of a single recommmendation.
	 */
	#[Route(path: '/{id}', methods: ['GET', 'HEAD'], name: 'details', options: ['expose' => true])]
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
