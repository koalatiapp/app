<?php

namespace App\Controller\Api;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/search", name="api_search")
 */
class SearchController extends AbstractApiController
{
	/**
	 * Contains the search results for the query.
	 *
	 * @var array<array<string>>
	 */
	protected $results = [];

	/**
	 * @Route("", methods={"POST"}, name="", options={"expose": true})
	 */
	public function search(Request $request): Response
	{
		$query = $request->request->get('query');
		$queryParts = $this->getPartsFromQuery($query);

		$this->searchProjects($queryParts);
		$this->searchOrganizations($queryParts);

		$this->sortResults($query);

		return $this->apiSuccess([
			'query' => $query,
			'results' => $this->results,
		]);
	}

	/**
	 * Returns the standardized query parts/words as an array of string.
	 *
	 * @return array<int,string>
	 */
	protected function getPartsFromQuery(string $query): array
	{
		return explode(' ', strtolower(trim($query)));
	}

	/**
	 * Adds a search result to the response.
	 */
	protected function addResult(string $url, string $title, ?string $snippet = null): void
	{
		$this->results[] = [
			'url' => $url,
			'title' => $title,
			'snippet' => $snippet,
		];
	}

	/**
	 * Sorts the result by placing the closest linguistic matches first.
	 */
	protected function sortResults(string $query): void
	{
		$truncatedQuery = strtolower(trim(substr($query, 0, 255)));

		usort($this->results, function ($resultA, $resultB) use ($truncatedQuery) {
			$levenshteinA = levenshtein(strtolower($resultA['title']), $truncatedQuery);
			$levenshteinB = levenshtein(strtolower($resultB['title']), $truncatedQuery);

			return $levenshteinA > $levenshteinB ? 1 : -1;
		});
	}

	/**
	 * Runs the search query on Projects.
	 *
	 * @param array<string> $queryParts
	 *
	 * @return void
	 */
	protected function searchProjects(array $queryParts)
	{
		/**
		 * @var \App\Repository\ProjectRepository
		 */
		$repository = $this->getDoctrine()->getRepository(Project::class);
		$projects = $repository->findBySearchQuery($queryParts, $this->getUser());

		foreach ($projects as $project) {
			$url = $this->generateUrl('project_dashboard', ['id' => $this->idHasher->encode($project->getId())]);
			$this->addResult($url, $project->getName(), $this->translator->trans('search.type.project'));
		}
	}

	/**
	 * Runs the search query on organizations/teams.
	 *
	 * @param array<string> $queryParts
	 *
	 * @return void
	 */
	protected function searchOrganizations(array $queryParts)
	{
		/** @var \App\Entity\OrganizationMember $link */
		foreach ($this->getUser()->getOrganizationLinks() as $link) {
			$organization = $link->getOrganization();

			foreach ($queryParts as $part) {
				if (strpos(strtolower($organization->getName()), strtolower($part)) === false) {
					continue 2;
				}
			}

			$url = $this->generateUrl('organization_dashboard', ['id' => $this->idHasher->encode($organization->getId())]);
			$this->addResult($url, $organization->getName(), $this->translator->trans('search.type.organization'));
		}
	}
}
