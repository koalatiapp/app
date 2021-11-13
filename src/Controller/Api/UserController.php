<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/user", name="api_user_")
 */
class UserController extends AbstractApiController
{
	/**
	 * Returns data about the current user.
	 *
	 * @Route("/current", methods={"GET"}, name="current", options={"expose": true})
	 */
	public function currentUser(): JsonResponse
	{
		return $this->apiSuccess($this->getUser(), ['self']);
	}
}
