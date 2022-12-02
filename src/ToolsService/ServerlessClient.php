<?php

namespace App\ToolsService;

use App\ToolsService\Exception\ServerlessClientNotConfiguredException;
use App\ToolsService\Exception\ToolsApiBadResponseException;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServerlessClient extends Client
{
	private bool $isConfigured = false;

	/**
	 * @param string $apiUrl         URL of the tools API
	 * @param string $apiBearerToken bearer token for the tools API
	 */
	public function __construct(?string $apiUrl, ?string $apiBearerToken)
	{
		if ($apiUrl && $apiBearerToken) {
			parent::__construct($apiUrl, $apiBearerToken);
			$this->isConfigured = true;
		}
	}

	/**
	 * Makes a request to the tools API and returns the response in the form of an array.
	 *
	 * @param string       $method   HTTP method of the request (GET, POST, PUT or DELETE)
	 * @param string       $endpoint API endpoint to query
	 * @param array<mixed> $body     Body of the request
	 *
	 * @throws ToolsApiBadResponseException
	 * @throws AccessDeniedHttpException
	 * @throws NotFoundHttpException
	 * @throws Exception
	 *
	 * @return array<mixed>
	 */
	public function request(string $method, string $endpoint, array $body = []): array
	{
		if (!$this->isConfigured) {
			throw new ServerlessClientNotConfiguredException();
		}

		$method = trim(strtoupper($method));
		$endpoint = '/'.ltrim($endpoint, '/');
		$options = ['json' => $body];
		$queryString = '';

		if ($method == 'GET') {
			$options = [];
			$queryString = http_build_query($body);
		}

		$url = rtrim($this->urlPrefix, "/").$endpoint;

		if ($queryString) {
			$queryPrefix = str_contains($endpoint, '?') ? '&' : '?';
			$url .= $queryPrefix.$queryString;
		}

		$response = $this->httpClient->request($method, $url, $options);

		$this->handleResponseErrors($response, $endpoint);

		return $response->toArray();
	}
}
