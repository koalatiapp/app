<?php

namespace App\Util\Favicon\Driver;

interface FaviconDriverInterface
{
	/**
	 * Fetches the image contents for the favicon of a page.
	 *
	 * @return string The contents of the favicon image
	 */
	public function fetch(string $url): string;
}
