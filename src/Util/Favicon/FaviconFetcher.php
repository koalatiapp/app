<?php

namespace App\Util\Favicon;

use App\Util\Favicon\Driver\FaviconDriverInterface;

class FaviconFetcher implements FaviconFetcherInterface
{
	private FaviconDriverInterface $driver;

	public function __construct(FaviconDriverInterface $driver)
	{
		$this->driver = $driver;
	}

	public function fetch(string $url): string
	{
		return $this->driver->fetch($url);
	}
}
