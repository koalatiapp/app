<?php

namespace App\Util\Favicon;

use App\Util\Favicon\Driver\FaviconDriverInterface;

interface FaviconFetcherInterface
{
	public function __construct(FaviconDriverInterface $driver);

	/**
	 * Fetches the favicon of a page, returning the highest resolution icon available.
	 */
	public function fetch(string $url): string;
}
