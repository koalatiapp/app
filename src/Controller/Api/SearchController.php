<?php

namespace App\Controller\Api;

use App\Util\Search\SearchEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/search", name="api_search")
 */
class SearchController extends AbstractApiController
{
	/**
	 * @Route("", methods={"POST"}, name="", options={"expose": true})
	 */
	public function search(Request $request, SearchEngine $searchEngine): Response
	{
		$query = $request->request->get('query');
		$results = $searchEngine->search($query, $this->getUser());

		return $this->apiSuccess([
			'query' => $query,
			'results' => $results,
		]);
	}
}
