<?php

namespace App\Util\Favicon\Driver;

use App\Util\Url;

class FaviconKit implements FaviconDriverInterface
{
	private Url $urlHelper;

	public function __construct(Url $urlHelper)
	{
		$this->urlHelper = $urlHelper;
	}

	public function fetch(string $url): string
	{
		$domain = $this->urlHelper::domain($url);
		$imageUrl = "https://api.faviconkit.com/$domain/32";

		return file_get_contents($imageUrl);
	}
}
