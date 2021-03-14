<?php

namespace App\ApiClient;

use App\ApiClient\Exception\ToolsApiBadResponseException;
use App\ApiClient\Exception\ToolsApiConfigurationException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Client implements ClientInterface
{
	/**
	 * @var \Symfony\Contracts\HttpClient\HttpClientInterface;
	 */
	protected $httpClient;

	public function __construct()
	{
		$this->validateConfiguration();
		$this->httpClient = HttpClient::createForBaseUri($_ENV['TOOLS_API_URL'], [
			'auth_bearer' => $_ENV['TOOLS_API_BEARER_TOKEN'],
		]);
	}

	/**
	 * Ensure that the required environment variables are defined to ues the client.
	 *
	 * @throws ToolsApiConfigurationException
	 *
	 * @return void
	 */
	private function validateConfiguration()
	{
		if (empty($_ENV['TOOLS_API_URL']) || empty($_ENV['TOOLS_API_BEARER_TOKEN'])) {
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
	 * @throws \Exception
	 *
	 * @return array<mixed>
	 */
	public function request(string $method, string $endpoint, array $body = []): array
	{
		$method = trim(strtoupper($method));
		$endpoint = '/'.ltrim($endpoint, '/');
		$options = $method == 'GET' ? [] : ['body' => $body];
		$response = $this->httpClient->request($method, $endpoint, $options);
		$statusCode = $response->getStatusCode();

		if ($statusCode == 403) {
			throw new AccessDeniedHttpException(sprintf('The tools API denied access to the "%s" endpoint. Make sure to provide a valid bearer token in the "TOOLS_API_BEARER_TOKEN" environment variable.', $endpoint));
		} elseif ($statusCode == 404) {
			throw new NotFoundHttpException(sprintf('The tools API endpoint "%s" could not be found. Make sure to provide a valid API URL in the "TOOLS_API_URL" environment variable.', $endpoint));
		} elseif ($statusCode != 200) {
			throw new \Exception(sprintf('An unknown error (code %s) occured in a request to the tools API endpoint "%s".', $statusCode, $endpoint), $statusCode);
		}

		$decodedResponse = $response->toArray();

		if (!$decodedResponse['success']) {
			throw new ToolsApiBadResponseException($decodedResponse['message'] ?? 'No error message was provided by the API.');
		}

		return $response->toArray();
	}
}
