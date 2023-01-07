<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Controller\Trait\PreventDirectAccessTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
	use PreventDirectAccessTrait;

	#[Route(path: '/internal-api/session-authentication', methods: ['GET'], name: 'api_session_authentication')]
	public function getAuthenticationTokenFromSession(JWTTokenManagerInterface $jwtManager): JsonResponse
	{
		return new JsonResponse([
			'token' => $jwtManager->create($this->getUser()),
		]);
	}
}
