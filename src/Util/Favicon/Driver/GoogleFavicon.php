<?php

namespace App\Util\Favicon\Driver;

use App\Util\Url;

class GoogleFavicon implements FaviconDriverInterface
{
	private Url $urlHelper;

	public function __construct(Url $urlHelper)
	{
		$this->urlHelper = $urlHelper;
	}

	public function fetch(string $url): string
	{
		$domain = $this->urlHelper::domain($url);
		$imageUrl = 'https://www.google.com/s2/favicons?sz=64&domain_url='.$domain;

		return file_get_contents($imageUrl);
	}
}
