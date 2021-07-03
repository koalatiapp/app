<?php

namespace App\Controller\Api\Testing;

use App\Controller\Api\AbstractApiController;
use App\Message\TestingRequest;
use App\Security\ProjectVoter;
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
	public function create(Request $request): JsonResponse
	{
		$projectId = $request->request->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId, ProjectVoter::PARTICIPATE);

		$this->dispatchMessage(new TestingRequest($project->getId()));

		return $this->apiSuccess();
	}
}
