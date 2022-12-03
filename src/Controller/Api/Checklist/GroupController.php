<?php

namespace App\Controller\Api\Checklist;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/internal-api/checklist/groups', name: 'api_checklist_group_')]
class GroupController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	/**
	 * Returns the list of groups for a given project's checklist.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 */
	#[Route(path: '', methods: ['GET', 'HEAD'], name: 'list', options: ['expose' => true])]
	public function list(Request $request): JsonResponse
	{
		$projectId = $request->query->get('project_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);

		if (!$this->isGranted(ProjectVoter::CHECKLIST, $project)) {
			return $this->accessDenied();
		}

		$checklist = $project->getChecklist();

		return $this->apiSuccess($checklist->getItemGroups());
	}
}
