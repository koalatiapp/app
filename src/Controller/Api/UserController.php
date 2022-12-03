<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/internal-api/user', name: 'api_user_')]
class UserController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	/**
	 * Returns data about the current user.
	 */
	#[Route(path: '/current', methods: ['GET'], name: 'current', options: ['expose' => true])]
	public function currentUser(): JsonResponse
	{
		return $this->apiSuccess($this->getUser(), ['self']);
	}
}
