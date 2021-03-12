<?php

namespace App\ApiClient;

interface ClientInterface
{
	/**
	 * Makes a request to the tools API and returns the response in the form of an array.
	 *
	 * @param string       $method   HTTP method of the request (GET, POST, PUT or DELETE)
	 * @param string       $endpoint API endpoint to query
	 * @param array<mixed> $body     Body of the request
	 *
	 * @return array<mixed>
	 */
	public function request(string $method, string $endpoint, array $body = []): array;
}
