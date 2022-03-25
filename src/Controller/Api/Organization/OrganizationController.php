<?php

namespace App\Controller\Api\Organization;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/organization/", name="api_organization_")
 */
class OrganizationController extends AbstractController
{
	use ApiControllerTrait;

	/**
	 * @Route("{id}", methods={"GET","HEAD"}, name="details", options={"expose": true})
	 */
	public function list(int $id): JsonResponse
	{
		$organization = $this->getOrganization($id);

		return $this->apiSuccess($organization);
	}
}
