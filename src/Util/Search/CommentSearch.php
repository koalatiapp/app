<?php

namespace App\Util\Search;

use App\Entity\User;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hashids\HashidsInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentSearch implements EntitySearchInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private UrlGeneratorInterface $router,
		private CommentRepository $commentRepository,
		private TranslatorInterface $translator,
		private HashidsInterface $idHasher
	) {
	}

	/**
	 * Runs the search query on Projects.
	 *
	 * @param array<string> $queryParts
	 * @param User|null     $user       if a user is specified, the search will be limited to
	 *                                  ressources that the user has access to
	 *
	 * @return Collection<int,SearchResult>
	 */
	public function search(array $queryParts, ?User $user = null): Collection
	{
		$results = new ArrayCollection();
		$comments = $this->commentRepository->findBySearchQuery($queryParts, $user);

		foreach ($comments as $comment) {
			$encodedProjectId = $this->idHasher->encode($comment->getProject()->getId());
			$encodedItemId = $this->idHasher->encode($comment->getChecklistItem()->getId());
			$encodedCommentId = $this->idHasher->encode($comment->getId());
			$checklistPageUrl = $this->router->generate('project_checklist', [
				'id' => $encodedProjectId,
				'search' => uniqid(),
			]);
			$url = $checklistPageUrl . "#item=$encodedItemId&comment=$encodedCommentId";

			if ($comment->getThread()) {
				$url .= "&thread=" . $this->idHasher->encode($comment->getThread()->getId());
			}

			$result = new SearchResult(
				$url,
				$comment->getTextContent(),
				$this->translator->trans('search.type.comment', ['%project%' => $comment->getProject()->getName()]),
			);

			$results->add($result);
		}

		return $results;
	}
}
