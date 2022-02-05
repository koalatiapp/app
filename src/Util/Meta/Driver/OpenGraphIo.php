<?php

namespace App\Util\Meta\Driver;

use App\Util\Meta\Metadata;
use App\Util\Meta\MetaDriverInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenGraphIo implements MetaDriverInterface
{
	private const API_URI = 'https://opengraph.io/api/1.1/site/';

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private string $apiKey,
		private HttpClientInterface $httpClient
	) {
	}

	public function getMetas(string $url): Metadata
	{
		$request = $this->httpClient->request('GET', self::API_URI.urlencode($url), [
			'query' => [
				'app_id' => $this->apiKey,
			],
		]);
		$results = $request->toArray();
		$rawMetas = $results['hybridGraph'] ?? [];

		return new Metadata(
			$url,
			$rawMetas['site_name'] ?? null,
			$rawMetas['title'] ?? null,
			$rawMetas['description'] ?? null,
			$rawMetas['image'] ?? null,
		);
	}
}
