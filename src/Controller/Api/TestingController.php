<?php

namespace App\Controller\Api;

use App\Repository\Testing\RecommendationRepository;
use App\Trait\ProjectControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_testing_")
 */
class TestingController extends AbstractApiController
{
	use ProjectControllerTrait;

	/**
	 * @Route("/recommendation/{recommendationId}", name="recommendation", options={"expose": true})
	 */
	public function recommendation(int $recommendationId, RecommendationRepository $recommendationRepository): JsonResponse
	{
		$recommendation = $recommendationRepository->find($recommendationId);

		if (!$recommendation) {
			return $this->apiError('This recommmendation does not exist.', 404);
		}

		$project = $recommendation->getProject();

		if (!$this->hasProjectAccess($project)) {
			return $this->accessDenied();
		}

		return $this->apiSuccess($this->simplifyEntity($recommendation));
	}
}
