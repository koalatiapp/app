<?php

namespace App\ApiClient;

use App\ApiClient\Exception\ToolsApiBadResponseException;
use App\ApiClient\Exception\ToolsApiConfigurationException;
use Exception;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Client implements ClientInterface
{
	protected HttpClientInterface $httpClient;

	protected string $urlPrefix = "";

	/**
	 * @param string $apiUrl         URL of the tools API
	 * @param string $apiBearerToken bearer token for the tools API
	 */
	public function __construct(string $apiUrl, string $apiBearerToken)
	{
		$this->validateConfiguration($apiUrl, $apiBearerToken);
		$this->httpClient = HttpClient::createForBaseUri($apiUrl, [
			'auth_bearer' => $apiBearerToken,
		]);
		$this->urlPrefix = parse_url($apiUrl, PHP_URL_PATH) ?: "";
	}

	/**
	 * Ensures that the required configurations are defined to use the client.
	 *
	 * @throws ToolsApiConfigurationException
	 */
	private function validateConfiguration(string $apiUrl, string $apiBearerToken): void
	{
		if (empty($apiUrl) || empty($apiBearerToken)) {
			throw new ToolsApiConfigurationException();
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
		$method = trim(strtoupper($method));
		$endpoint = '/'.ltrim($endpoint, '/');
		$options = ['body' => $body];
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

	/**
	 * @throws Exception
	 * @throws UnauthorizedHttpException
	 * @throws NotFoundHttpException
	 * @throws ToolsApiBadResponseException
	 */
	protected function handleResponseErrors(ResponseInterface $response, string $endpoint): void
	{
		$statusCode = $response->getStatusCode();

		if ($statusCode == 401 || $statusCode == 403) {
			throw new UnauthorizedHttpException('The tools API denied access because your request lacked proper authorization. Make sure to provide a valid bearer token.');
		} elseif ($statusCode == 404) {
			throw new NotFoundHttpException(sprintf('The tools API endpoint "%s" could not be found. Make sure to provide a valid API URL.', $endpoint));
		} elseif ($statusCode != 200) {
			throw new Exception(sprintf('An unknown error (code %s) occured in a request to the tools API endpoint "%s".', $statusCode, $endpoint), $statusCode);
		}

		$decodedResponse = $response->toArray();

		if (!$decodedResponse['success']) {
			throw new ToolsApiBadResponseException($decodedResponse['message'] ?? 'No error message was provided by the API.');
		}
	}
}
