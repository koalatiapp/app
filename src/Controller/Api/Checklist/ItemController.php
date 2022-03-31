<?php

namespace App\Controller\Api\Checklist;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Entity\Checklist\ItemGroup;
use App\Mercure\UpdateType;
use App\Repository\Checklist\ItemRepository;
use App\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/checklist/items", name="api_checklist_item_")
 */
class ItemController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	/**
	 * Returns the list of items for a given project's checklist.
	 *
	 * Available query parameters:
	 * - `project_id` - `int` (required)
	 * - `group_id` - `int` (optional)
	 *
	 * @Route("", methods={"GET","HEAD"}, name="list", options={"expose": true})
	 */
	public function list(Request $request): JsonResponse
	{
		$projectId = $request->query->get('project_id');
		$groupId = $request->query->get('group_id');

		if (!$projectId) {
			return $this->apiError('You must provide a valid value for `project_id`.');
		}

		$project = $this->getProject($projectId);
		$checklist = $project->getChecklist();

		if ($groupId) {
			if (!is_numeric($groupId)) {
				$groupId = $this->idHasher->decode($groupId)[0];
			}

			/** @var ItemGroup */
			$group = $checklist->getItemGroups()->filter(fn ($group) => $group->getId() == $groupId)->first();

			return $this->apiSuccess($group->getItems());
		}

		return $this->apiSuccess($checklist->getItems());
	}

	/**
	 * Toggles an item's completion state.
	 *
	 * @Route("/{id}/toggle", methods={"PUT", "POST"}, name="toggle", options={"expose": true})
	 */
	public function toggleItemCompletion(int $id, Request $request, ItemRepository $itemRepository): JsonResponse
	{
		$item = $itemRepository->find($id);

		if (!$item) {
			return $this->notFound();
		}

		$project = $item->getChecklist()->getProject();

		if (!$this->isGranted(ProjectVoter::PARTICIPATE, $project)) {
			return $this->accessDenied();
		}

		$completed = $request->request->get('is_completed');
		$item->setIsCompleted($completed);

		$em = $this->getDoctrine()->getManager();
		$em->persist($item);
		$em->flush();

		$this->updateDispatcher->dispatch($item, UpdateType::UPDATE);

		return $this->apiSuccess($item);
	}
}
