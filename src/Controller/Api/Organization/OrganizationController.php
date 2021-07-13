<?php

namespace App\Controller\Api\Organization;

use App\Controller\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/organization", name="api_organization_")
 */
class OrganizationController extends AbstractApiController
{
	/**
	 * @Route("{id}", methods={"GET","HEAD"}, name="details", options={"expose": true})
	 */
	public function list(int $id): JsonResponse
	{
		$organization = $this->getOrganization($id);

		return $this->apiSuccess($organization);
	}
}
