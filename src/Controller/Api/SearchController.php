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

		return $this->apiSuccess([
			'query' => $query,
			'results' => $this->results,
		]);
	}

	/**
	 * Returns the standardized query parts/words as an array of string.
	 *
	 * @return array<string>
	 */
	protected function getPartsFromQuery(string $query)
	{
		return explode(' ', strtolower(trim($query)));
	}

	/**
	 * Adds a search result to the response.
	 *
	 * @return void
	 */
	protected function addResult(string $url, string $title, ?string $snippet = null)
	{
		$this->results[] = [
			'url' => $url,
			'title' => $title,
			'snippet' => $snippet,
		];
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
			$url = $this->generateUrl('project_dashboard', ['id' => $project->getId()]);
			$this->addResult($url, $project->getName());
		}
	}
}
