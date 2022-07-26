<?php

namespace App\Controller\Api\Testing;

use App\ApiClient\Endpoint\StatusEndpoint;
use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Message\TestingRequest;
use App\MessageHandler\TestingRequestHandler;
use App\Security\ProjectVoter;
use App\Util\Testing\TestingStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/testing/request", name="api_testing_request_")
 */
class TestingController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	/**
	 * Submits an automated testing request for the project.
	 * Testing will be requested for every page and for every tool.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 *
	 * @Route("/create", methods={"POST"}, name="create", options={"expose": true})
	 */
	public function create(Request $request, TestingRequestHandler $testingRequestHandler): JsonResponse
	{
		$projectId = $request->request->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$projectId = $this->idHasher->decode($projectId)[0];
		$project = $this->getProject($projectId, ProjectVoter::PARTICIPATE);

		$this->denyAccessUnlessGranted(ProjectVoter::TESTING, $project);

		// In this case, we don't want to put this in the message queue and wait.
		// We want the testing to start right now!
		$testingRequestHandler(new TestingRequest($project->getId()));

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

		return $this->apiSuccess(new TestingStatus($project, $status));
	}
}
