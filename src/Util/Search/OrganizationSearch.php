<?php

namespace App\Util\Search;

use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Hashids\HashidsInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OrganizationSearch implements EntitySearchInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly UrlGeneratorInterface $router,
		private readonly OrganizationRepository $organizationRepository,
		private readonly TranslatorInterface $translator,
		private readonly SluggerInterface $slugger,
		private readonly HashidsInterface $idHasher
	) {
	}

	/**
	 * Runs the search query on Organizations.
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
		$snippet = $this->translator->trans('search.type.organization');
		$organizations = $this->getAvailableOrganizations($user);
		$sluggedQueryParts = array_map(function (string $queryPart) {
			return strtolower($this->slugger->slug($queryPart, ' '));
		}, $queryParts);

		foreach ($organizations as $organization) {
			$sluggedOrganizationName = strtolower($this->slugger->slug($organization->getName(), ' '));

			// Apply the actual search
			foreach ($sluggedQueryParts as $part) {
				if (!str_contains($sluggedOrganizationName, $part)) {
					continue 2;
				}
			}

			$encodedId = $this->idHasher->encode($organization->getId());
			$url = $this->router->generate('organization_dashboard', ['id' => $encodedId]);
			$result = new SearchResult($url, $organization->getName(), $snippet);
			$results->add($result);
		}

		return $results;
	}

	/**
	 * Returns the pool of available organizations in which to search.
	 *
	 * @param User|null $user if a user is specified, the search will be limited to
	 *                        ressources that the user has access to
	 *
	 * @return array<int,Organization>
	 */
	private function getAvailableOrganizations(?User $user = null): array
	{
		if (!$user) {
			return $this->organizationRepository->findAll();
		}

		$organizations = [];

		foreach ($user->getOrganizationLinks() as $link) {
			$organizations[] = $link->getOrganization();
		}

		return $organizations;
	}
}
