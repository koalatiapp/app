<?php

namespace App\Util\Meta\Driver;

use App\Util\Meta\Metadata;
use App\Util\Meta\MetaDriverInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UrlMeta implements MetaDriverInterface
{
	private const API_URI = 'https://api.urlmeta.org/';

	private HttpClientInterface $httpClient;

	public function __construct(string $accountEmail, string $apiKey)
	{
		$this->httpClient = HttpClient::createForBaseUri(static::API_URI, [
			'auth_basic' => $this->generateAuthorization($accountEmail, $apiKey),
		]);
	}

	private function generateAuthorization(string $accountEmail, string $apiKey): string
	{
		return base64_encode("$accountEmail:$apiKey");
	}

	public function getMetas(string $url): Metadata
	{
		$request = $this->httpClient->request('GET', self::API_URI.urlencode($url));
		$results = $request->toArray();
		$rawMetas = $results['meta'] ?? [];

		return new Metadata(
			$url,
			$rawMetas['site']['name'] ?? null,
			$rawMetas['title'] ?? null,
			$rawMetas['description'] ?? null,
			$rawMetas['image'] ?? null,
		);
	}
}
