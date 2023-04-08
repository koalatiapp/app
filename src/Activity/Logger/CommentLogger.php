<?php

namespace App\Activity\Logger;

use App\Activity\AbstractEntityActivityLogger;
use App\Entity\Comment;

/**
 * @extends AbstractEntityActivityLogger<Comment>
 */
class CommentLogger extends AbstractEntityActivityLogger
{
	public static function getEntityClass(): string
	{
		return Comment::class;
	}

	public function postPersist(object &$comment, ?array $originalData): void
	{
		// Create activity log
		$activityType = $comment->getThread() ? "comment_reply" : "comment_create";

		if (isset($originalData['isResolved']) && $originalData['isResolved'] != $comment->isResolved()) {
			$activityType = $comment->isResolved() ? "comment_resolved" : "comment_unresolved";
		}

		$this->log(
			type: $activityType,
			organization: $comment->getProject()->getOwnerOrganization(),
			project: $comment->getProject(),
			target: $comment,
		);
	}

	public function postRemove(object &$comment): void
	{
		$this->log(
			type: "comment_delete",
			organization: $comment->getProject()->getOwnerOrganization(),
			project: $comment->getProject(),
			target: $comment->getChecklistItem(),
		);
	}
}
