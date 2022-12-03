<?php

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Controller\Trait\ApiControllerTrait;
use App\Controller\Trait\PreventDirectAccessTrait;
use App\Util\Search\SearchEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/internal-api/search', name: 'api_search')]
class SearchController extends AbstractController
{
	use ApiControllerTrait;
	use PreventDirectAccessTrait;

	#[Route(path: '', methods: ['POST'], name: '', options: ['expose' => true])]
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
