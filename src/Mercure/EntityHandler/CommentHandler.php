<?php

namespace App\Mercure\EntityHandler;

use App\Entity\Comment;
use App\Mercure\EntityHandlerInterface;
use App\Mercure\MercureEntityInterface;

class CommentHandler implements EntityHandlerInterface
{
	public function getSupportedEntity(): string
	{
		return Comment::class;
	}

	public function getType(): string
	{
		return "Comment";
	}

	/**
	 * @param Comment $comment
	 */
	public function getAffectedUsers(MercureEntityInterface $comment): array
	{
		return (new ProjectHandler())->getAffectedUsers($comment->getProject());
	}
}
