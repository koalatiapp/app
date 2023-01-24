<?php

namespace App\Util\Search;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Search engine for routes / sections of the Koalati application.
 */
class RouteSearch implements SearchInterface
{
	public const INDEXED_ROUTES = [
		"dashboard",
		"projects",
		"help",
		"onboarding",
		"edit_profile",
		"manage_account_api",
		"manage_account_security",
		"manage_subscription",
		"manage_subscription_quota",
	];

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private readonly UrlGeneratorInterface $router,
		private readonly TranslatorInterface $translator,
		private readonly SluggerInterface $slugger,
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
		$sluggedQueryParts = array_map(function (string $queryPart) {
			return strtolower((string) $this->slugger->slug($queryPart, ' '));
		}, $queryParts);

		foreach (self::INDEXED_ROUTES as $route) {
			$title = $this->translator->trans("search.route.$route.title");
			$description = $this->translator->trans("search.route.$route.description");
			$sluggedTitle = strtolower((string) $this->slugger->slug($title, ' '));
			$sluggedDescription = strtolower((string) $this->slugger->slug($title, ' '));

			// Apply the actual search
			foreach ($sluggedQueryParts as $part) {
				if (!str_contains("$sluggedTitle $sluggedDescription", $part)) {
					continue 2;
				}
			}

			$url = $this->router->generate($route);
			$result = new SearchResult($url, $title, $description);
			$results->add($result);
		}

		return $results;
	}
}
