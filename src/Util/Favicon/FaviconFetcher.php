<?php

namespace App\Util\Favicon;

use App\Util\Favicon\Driver\FaviconDriverInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

class FaviconFetcher implements FaviconFetcherInterface
{
	private LoggerInterface $logger;

	/**
	 * @param array<int,FaviconDriverInterface> $drivers
	 */
	public function __construct(
		private array $drivers
	) {
	}

	/**
	 * @required
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	public function fetch(string $url): string
	{
		foreach ($this->drivers as $driver) {
			try {
				return $driver->fetch($url);
			} catch (Throwable $exception) {
				$this->logger->error($exception, debug_backtrace());
			}
		}

		throw new Exception(sprintf("Could not fetch favicon for '$url'. %d favicon drivers were attempted, and they all failed.", count($this->drivers)));
	}
}
