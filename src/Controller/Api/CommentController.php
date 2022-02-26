<?php

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Mercure\TopicBuilder;
use App\Repository\Checklist\ItemRepository;
use App\Repository\CommentRepository;
use App\Security\ProjectVoter;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\HashidsInterface;
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
	public function list(Request $request, ItemRepository $itemRepository, HashidsInterface $idHasher): JsonResponse
	{
		$projectId = $request->query->get('project_id');
		$itemId = $request->query->get('checklist_item_id');

		if (!$projectId && !$itemId) {
			return $this->apiError('You must provide a valid value for `project_id` or `checklist_item_id`.');
		}

		// Decode IDs
		$projectId = $projectId ? $idHasher->decode($projectId)[0] : $projectId;
		$itemId = $itemId ? $idHasher->decode($itemId)[0] : $itemId;

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
	 * @Route("", methods={"POST","PUT"}, name="submit", options={"expose": true})
	 */
	public function submit(Request $request, CommentRepository $commentRepository, ItemRepository $itemRepository, EntityManagerInterface $entityManager): JsonResponse
	{
		$projectId = $request->request->get('project_id');

		if (!$projectId) {
			return $this->badRequest('You must provide a valid value for `project_id`.');
		}

		$itemId = $request->request->get('checklist_item_id');
		$threadId = $request->request->get('thread_id');
		$content = $request->request->get('content');
		$project = $this->getProject($this->idHasher->decode($projectId)[0], ProjectVoter::PARTICIPATE);

		$comment = (new Comment())
			->setAuthor($this->getUser())
			->setProject($project)
			->setContent($content) // @TODO: Sanitize content before saving to database
		;

		if ($itemId) {
			$item = $itemRepository->find($this->idHasher->decode($itemId)[0]);

			if (!$item) {
				return $this->notFound('Checklist item (`checklist_item_id`) not found.');
			}

			if ($item->getChecklist()->getProject() != $project) {
				return $this->badRequest('Checklist item (`checklist_item_id`) is not a part of project (`project_id`).');
			}

			$comment->setChecklistItem($item);
		}

		if ($threadId) {
			dump($threadId);
			$thread = $commentRepository->find($this->idHasher->decode($threadId)[0]);

			if (!$thread) {
				return $this->notFound('Thread (`thread_id`) not found.');
			}

			if ($thread->getProject() != $project) {
				return $this->badRequest('Thread (`thread_id`) is not a part of project (`project_id`).');
			}

			$comment->setThread($thread);
		}

		$entityManager->persist($comment);
		$entityManager->flush();

		return $this->apiSuccess($comment);
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

	/**
	 * @Route("/{id}/resolve", methods={"PATCH"}, name="resolve", options={"expose": true})
	 */
	public function resolve(int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager): JsonResponse
	{
		/** @var Comment|null */
		$comment = $commentRepository->find($id);

		if (!$comment) {
			return $this->notFound();
		}

		if (!$this->isGranted(ProjectVoter::VIEW, $comment->getProject())) {
			return $this->accessDenied();
		}

		$comment->setIsResolved(true);
		$entityManager->persist($comment);
		$entityManager->flush();

		return $this->apiSuccess($comment);
	}
}
