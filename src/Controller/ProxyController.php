<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProxyController extends AbstractController
{
	#[Route(path: '/image-proxy', name: 'image_proxy')]
	public function proxy(Request $request, HttpClientInterface $httpClient): Response
	{
		$url = $request->query->get("url");

		if (!$url) {
			return new Response("Missing image URL.", 400);
		}

		$url = urldecode($url);

		// If the URL is a Koalati image proxy URL... try to get to the real root URL
		while (str_contains($url, '/image-proxy')) {
			$underlyingRequest = Request::create($url);
			$underlyingUrl = $url;
			$url = $underlyingRequest->query->get("url");

			if (!$url) {
				return new Response("Missing image URL in underlying proxy request\n$underlyingUrl.", 400);
			}

			$url = urldecode($url);
		}

		$sourceResponse = $httpClient->request("GET", $url, [
				"timeout" => 2.5,
				"max_redirects" => 10,
				"headers" => [
					"Accept" => "application/json",
				],
			]);
		$statusCode = $sourceResponse->getStatusCode();

		if ($statusCode < 200 || $statusCode >= 300) {
			return new Response("Could not fetch image", $statusCode);
		}

		$contentType = $sourceResponse->getHeaders()['content-type'][0];

		if (!str_contains(strtolower($contentType), "image/")) {
			return new Response("Invalid file type - expecting an image.", 400);
		}

		$response = new Response($sourceResponse->getContent(), 200, ["Content-Type" => $contentType]);
		$response->setPublic();
		$response->setMaxAge(3600);

		return $response;
	}
}
