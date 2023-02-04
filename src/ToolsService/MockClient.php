<?php

namespace App\ToolsService;

use App\Util\Url;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MockClient extends Client
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct()
	{
		$this->httpClient = new MockHttpClient(function (string $method, string $url) {
			$endpoint = Url::path($url);
			$body = null;

			switch ($endpoint) {
				case '/status/up':
					$body = [
						'success' => true,
						'uptime' => 15000,
					];
					break;

				case '/status/queue':
					$body = [
						'success' => true,
						'message' => '',
						'data' => [
							'unassignedRequests' => 0,
							'pendingRequests' => 1,
						],
					];
					break;

				case '/status/time-estimates':
					$body = [
						'success' => true,
						'message' => '',
						'data' => [
							'@koalati/tool-seo' => [
								'processing_time' => 3000,
							],
						],
					];
					break;

				case '/tools/request':
					$body = [
						'success' => true,
					];
					break;
			}

			if ($body === null) {
				return new MockResponse('404', ['http_code' => 404]);
			}

			return new MockResponse(json_encode($body, JSON_THROW_ON_ERROR), ['response_headers' => ['Content-Type' => 'application/json']]);
		}, 'http://domain.com');
	}
}
