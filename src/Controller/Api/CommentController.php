<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Mercure\TopicBuilder;
use App\Repository\Checklist\ItemRepository;
use App\Repository\CommentRepository;
use App\Security\ProjectVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/comments", name="api_comments_")
 */
class CommentController extends AbstractApiController
{
	/**
	 * @Route("", methods={"GET","HEAD"}, name="list", options={"expose": true})
	 */
	public function list(Request $request, ItemRepository $itemRepository): JsonResponse
	{
		$projectId = $request->query->get('project_id');
		$itemId = $request->query->get('checklist_item_id');

		if (!$projectId && !$itemId) {
			return $this->apiError('You must provide a valid value for `project_id` or `checklist_item_id`.');
		}

		if (!$projectId) {
			$item = $itemRepository->find($itemId);
			$projectId = $item->getParentGroup()->getChecklist()->getProject()->getId();
		}

		$project = $this->getProject($projectId);
		$comments = $project->getComments();

		if ($itemId) {
			$comments = $comments->filter(fn (Comment $comment) => $comment->getChecklistItem()->getId() == $itemId);
		}

		return $this->apiSuccess($comments);
	}

	/**
	 * @Route("/{id}", methods={"GET","HEAD"}, name="details", options={"expose": true})
	 */
	public function details(int $id, CommentRepository $commentRepository): JsonResponse
	{
		/** @var Comment|null */
		$comment = $commentRepository->find($id);

		if (!$comment) {
			return $this->notFound();
		}

		if (!$this->isGranted(ProjectVoter::VIEW, $comment->getProject())) {
			return $this->accessDenied();
		}

		$this->setSuggestedMercureTopic($this->topicBuilder->getEntityTopic($comment, TopicBuilder::SCOPE_SPECIFIC));

		return $this->apiSuccess($comment);
	}
}
