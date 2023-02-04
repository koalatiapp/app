<?php

namespace App\Util\Favicon\Driver;

use App\Util\Url;

class FaviconKit implements FaviconDriverInterface
{
	public function __construct(
		private readonly Url $urlHelper,
		private readonly string $apiHostname,
	) {
	}

	public function fetch(string $url): string
	{
		if (!$this->apiHostname) {
			throw new \Exception("FaviconKit API hostname not configured. Please provide a `FAVICONKIT_API_HOSTNAME` environment variable.");
		}

		$hostname = $this->apiHostname;
		$domain = $this->urlHelper::domain($url);
		$imageUrl = "https://$hostname/$domain/32";

		return file_get_contents($imageUrl);
	}
}
