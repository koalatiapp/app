<?php

namespace App\Controller\Api\Project;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/projects", name="api_projects_")
 */
class ProjectController extends AbstractController
{
	use ApiControllerTrait;

	/**
	 * @Route("", methods={"GET","HEAD"}, name="list", options={"expose": true})
	 */
	public function list(Request $request): JsonResponse
	{
		$ownerType = $request->query->get('owner_type');
		$organizationId = $request->query->get('owner_organization_id');

		if (!$ownerType && $organizationId) {
			$ownerType = 'organization';
		}

		if (!$ownerType) {
			return $this->apiSuccess($this->getUser()->getAllProjects());
		}

		if ($ownerType == 'user') {
			return $this->apiSuccess($this->getUser()->getPersonalProjects());
		}

		if (!$organizationId) {
			return $this->apiError('You must provide a valid value for `owner_organization_id`, or use a different `owner_type`.');
		}

		$organization = $this->getOrganization($organizationId);

		return $this->apiSuccess($organization->getProjects());
	}
}
