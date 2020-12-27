<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    /**
     * @Route("/json/search", name="json_search", options={"expose": true})
     */
    public function jsonSearch(Request $request): Response
    {
		$query = $request->request->get('query');

		// @TODO: Implement the search feature using the format below.
		$results = [
			/*
			[
				"url" => "",
				"title" => "",
				"snippet" => "",
			],
			*/
		];

		return $this->json([
			'query' => $query,
			'results' => $results,
		]);
    }
}
