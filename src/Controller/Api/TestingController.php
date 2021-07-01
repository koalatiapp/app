<?php

namespace App\Controller\Api;

use App\Message\TestingRequest;
use App\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/testing", name="api_testing_")
 */
class TestingController extends AbstractApiController
{
	/**
	 * Submits an automated testing request for the project.
	 * Testing will be requested for every page and for every tool.
	 *
	 * @Route("/request/{projectId}", methods={"POST"}, name="request", options={"expose": true})
	 */
	public function testingRequest(int $projectId): JsonResponse
	{
		$project = $this->getProject($projectId, ProjectVoter::PARTICIPATE);

		$this->dispatchMessage(new TestingRequest($project->getId()));

		return $this->apiSuccess();
	}
}
