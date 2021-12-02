<?php

namespace App\Controller\Api\Testing;

use App\ApiClient\Endpoint\StatusEndpoint;
use App\Controller\Api\AbstractApiController;
use App\Message\TestingRequest;
use App\Security\ProjectVoter;
use App\Subscription\QuotaManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/testing/request", name="api_testing_request_")
 */
class TestingController extends AbstractApiController
{
	/**
	 * Submits an automated testing request for the project.
	 * Testing will be requested for every page and for every tool.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 *
	 * @Route("/create", methods={"POST"}, name="create", options={"expose": true})
	 */
	public function create(Request $request, QuotaManager $quotaManager): JsonResponse
	{
		$projectId = $request->request->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$projectId = $this->idHasher->decode($projectId)[0];
		$project = $this->getProject($projectId, ProjectVoter::PARTICIPATE);

		$this->denyAccessUnlessGranted(ProjectVoter::TESTING, $project);
		$this->dispatchMessage(new TestingRequest($project->getId()));

		$quotaManager->notifyIfQuotaExceeded($project);

		return $this->apiSuccess();
	}

	/**
	 * Returns data about the project's testing status.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 *
	 * @Route("/project-status/{id}", methods={"GET"}, name="project_status", options={"expose": true})
	 */
	public function projectStatus(string $id, StatusEndpoint $statusApi): JsonResponse
	{
		$project = $this->getProject($id);

		$this->denyAccessUnlessGranted(ProjectVoter::TESTING, $project);

		$status = $statusApi->project($project);

		return $this->apiSuccess($status);
	}
}
