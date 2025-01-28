<?php

namespace App\Controller\User;

use App\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityApiController extends AbstractController
{
	#[Route(path: '/account/security/api', name: 'manage_account_api')]
	public function apiSettings(): Response
	{
		return $this->render('app/user/security/api_settings.html.twig', [
		]);
	}
}
