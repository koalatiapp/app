<?php

namespace App\Util\Search;

use App\Entity\User;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hashids\HashidsInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectSearch implements EntitySearchInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private UrlGeneratorInterface $router,
		private ProjectRepository $projectRepository,
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
		$projects = $this->projectRepository->findBySearchQuery($queryParts, $user);
		$snippet = $this->translator->trans('search.type.project');

		foreach ($projects as $project) {
			$encodedId = $this->idHasher->encode($project->getId());
			$url = $this->router->generate('project_dashboard', ['id' => $encodedId]);
			$result = new SearchResult($url, $project->getName(), $snippet);
			$results->add($result);
		}

		return $results;
	}
}
