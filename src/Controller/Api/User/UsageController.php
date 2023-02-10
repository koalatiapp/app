<?php

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Subscription\UsageManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/internal-api/user/usage', name: 'api_user_usage_')]
class UsageController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	public function __construct(
		private UsageManager $usageManager,
	) {
	}

	#[Route(path: '/historical', methods: ['GET', 'HEAD'], name: 'historical', options: ['expose' => true])]
	public function historicalUsage(): JsonResponse
	{
		return $this->apiSuccess(
			$this->usageManager->getHistoricalUsage()
		);
	}
}
