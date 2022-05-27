<?php

namespace App\ApiClient;

use App\Util\Url;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MockServerlessClient extends ServerlessClient
{
	/** @SuppressWarnings(PHPMD.UnusedFormalParameter.method) */
	public function __construct()
	{
		$this->httpClient = new MockHttpClient(function (string $method, string $url) {
			$endpoint = Url::path($url);
			$body = null;

			switch ($endpoint) {
				case '/project-status':
					$body = [
						'success' => true,
						'message' => '',
						'data' => [
							'pending' => false,
							'requestCount' => 1,
							'timeEstimate' => null,
						],
					];
					break;

				case '/request':
					$body = [
						'success' => true,
						'requestsAdded' => 8,
					];
					break;
			}

			if ($body === null) {
				return new MockResponse('404', ['http_code' => 404]);
			}

			return new MockResponse(json_encode($body), ['response_headers' => ['Content-Type' => 'application/json']]);
		}, 'http://domain.com');
	}
}
