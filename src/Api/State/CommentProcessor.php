<?php

namespace App\Api\State;

use App\Entity\Comment;
use App\Mercure\UpdateType;
use App\Security\ProjectVoter;
use App\Util\HtmlSanitizer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends AbstractDoctrineStateWrapper<Comment>
 */
class CommentProcessor extends AbstractDoctrineStateWrapper
{
	public function __construct(
		private HtmlSanitizer $htmlSanitizer,
	) {
	}

	/**
	 * Hook before the persistence of a resource in the database.
	 *
	 * @param Comment $comment
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.originalData)
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 * @SuppressWarnings(PHPMD.NPathComplexity.originalData)
	 */
	protected function prePersist(object &$comment, ?array $originalData): void
	{
		$comment->setContent($this->htmlSanitizer->sanitize($comment->getContent()));
		$comment->setAuthor($this->getUser());

		if (!$comment->getChecklistItem()) {
			if (!$comment->getThread()) {
				throw new BadRequestHttpException("Missing IRI for checklist_item.");
			}

			$comment->setChecklistItem($comment->getThread()->getChecklistItem());
		}

		if (!$comment->getProject()) {
			$comment->setProject($comment->getChecklistItem()->getChecklist()->getProject());
		}

		if ($comment->getThread()) {
			if ($comment->getThread()->getChecklistItem() != $comment->getChecklistItem()) {
				throw new BadRequestHttpException("Thread is within a different item than the one specified in checklist_item.");
			}

			$parentComment = $comment->getThread();

			while ($parentComment->getThread()) {
				$parentComment = $parentComment->getThread();
			}

			$comment->setThread($parentComment);
		}

		if ($comment->getChecklistItem()->getChecklist()->getProject() != $comment->getProject()) {
			throw new BadRequestHttpException("Project does not match the checklist item's project.");
		}

		if (!$this->security->isGranted(ProjectVoter::CHECKLIST, $comment->getProject())) {
			throw new AccessDeniedException();
		}

		if (!$this->security->isGranted(ProjectVoter::PARTICIPATE, $comment->getProject())) {
			throw new AccessDeniedException();
		}
	}

	/**
	 * Hook after the persistence of a resource in the database.
	 *
	 * @param Comment $comment
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.originalData)
	 */
	protected function postPersist(object &$comment, ?array $originalData): void
	{
		if ($comment->getChecklistItem()) {
			$this->mercureUpdateDispatcher->dispatch($comment->getChecklistItem(), UpdateType::UPDATE);
		}
	}
}
