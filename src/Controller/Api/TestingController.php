<?php

namespace App\Controller\Api;

use App\Entity\Testing\IgnoreEntry;
use App\Message\TestingRequest;
use App\Repository\Testing\RecommendationRepository;
use App\Security\ProjectVoter;
use App\Trait\ProjectControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
		try {
			$project = $this->getProject($projectId);
		} catch (NotFoundHttpException $exception) {
			return $this->notFound();
		}

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
		try {
			$project = $this->getProject($projectId);
		} catch (NotFoundHttpException $exception) {
			return $this->notFound();
		}

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

		try {
			$project = $recommendation->getProject();
		} catch (NotFoundHttpException $exception) {
			return $this->notFound();
		}

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

		try {
			$project = $recommendation->getProject();
		} catch (NotFoundHttpException $exception) {
			return $this->notFound();
		}

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

		try {
			$project = $recommendation->getProject();
		} catch (NotFoundHttpException $exception) {
			return $this->notFound();
		}

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

	/**
	 * Marks all recommendations from a given group as completed.
	 *
	 * @Route("/recommendation/ignore/create", methods={"POST","PUT"}, name="recommendation_ignore", options={"expose": true})
	 */
	public function createIgnoreEntry(Request $request, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$scope = $request->request->get('scope');
		$recommendationId = $request->request->get('recommendation_id');
		$recommendation = $recommendationRepository->find($recommendationId);

		if (!$recommendation) {
			// @TODO: replace this error message with a translation message
			return $this->notFound('The recommendation you are attempting to ignore does not seem to exist anymore.');
		}

		try {
			$project = $recommendation->getProject();
		} catch (NotFoundHttpException $exception) {
			return $this->notFound();
		}

		if (!$this->isGranted(ProjectVoter::PARTICIPATE, $project)) {
			return $this->accessDenied();
		}

		$testResult = $recommendation->getParentResult();
		$toolResponse = $testResult->getParentResponse();
		$ignoreEntry = new IgnoreEntry($toolResponse->getTool(), $testResult->getUniqueName(), $recommendation->getUniqueName());
		$ignoreEntry->setCreatedBy($this->getUser());

		switch ($scope) {
			case 'organization':
				$ignoreEntry->setTargetOrganization($project->getOwnerOrganization());
				break;
			case 'user':
				$ignoreEntry->setTargetUser($this->getUser());
				break;
			case 'project':
				$ignoreEntry->setTargetProject($project);
				break;
			case 'page':
				$ignoreEntry->setTargetPage($recommendation->getRelatedPage());
				break;
		}

		$em->persist($ignoreEntry);
		$em->flush();

		return $this->apiSuccess($ignoreEntry);
	}
}
