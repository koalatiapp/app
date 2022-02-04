<?php

namespace App\Util\Favicon;

use App\Util\Favicon\Driver\FaviconDriverInterface;

interface FaviconFetcherInterface
{
	/**
	 * @param array<int,FaviconDriverInterface> $drivers
	 */
	public function __construct(array $drivers);

	/**
	 * Fetches the favicon of a page, returning the highest resolution icon available.
	 */
	public function fetch(string $url): string;
}
