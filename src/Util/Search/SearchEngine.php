<?php

namespace App\Util\Search;

use App\Entity\User;

class SearchEngine
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private ProjectSearch $projectSearch,
		private OrganizationSearch $organizationSearch,
	) {
	}

	/**
	 * Searches through all of the available ressources to find results
	 * matching the provided search query.
	 *
	 * @param User|null $user if a user is specified, the search will be limited to
	 *                        ressources that the user has access to
	 *
	 * @return array<int,SearchResult>
	 */
	public function search(string $query, ?User $user = null): array
	{
		$queryParts = $this->splitQueryIntoParts($query);

		$projectResults = $this->projectSearch->search($queryParts, $user);
		$organizationResults = $this->organizationSearch->search($queryParts, $user);
		$results = [
			...$projectResults,
			...$organizationResults,
		];

		$sortedResults = $this->sortResults($results, $query);

		return $sortedResults;
	}

	/**
	 * Returns the standardized query parts/words as an array of strings.
	 *
	 * @return array<int,string>
	 */
	protected function splitQueryIntoParts(string $query): array
	{
		return explode(' ', strtolower(trim($query)));
	}

	/**
	 * Sorts the result by placing the closest linguistic matches first.
	 *
	 * @param array<int,SearchResult> $results
	 *
	 * @return array<int,SearchResult>
	 */
	protected function sortResults(array $results, string $query): array
	{
		$truncatedQuery = strtolower(trim(substr($query, 0, 255)));

		usort($results, function (SearchResult $resultA, SearchResult $resultB) use ($truncatedQuery) {
			$levenshteinA = levenshtein(strtolower($resultA->title), $truncatedQuery);
			$levenshteinB = levenshtein(strtolower($resultB->title), $truncatedQuery);

			// Sort by linguistic match first
			if ($levenshteinA != $levenshteinB) {
				return $levenshteinA > $levenshteinB ? 1 : -1;
			}

			// Sort by title as a fallback
			$titleSortResult = strnatcasecmp($resultA->title, $resultB->title);

			if ($titleSortResult != 0) {
				return $titleSortResult;
			}
			// Sort by snippet as a last resort
			return strnatcasecmp($resultA->snippet, $resultB->snippet);
		});

		return $results;
	}
}
