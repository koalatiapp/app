<?php

namespace App\Util\Meta;

use Exception;
use Psr\Log\LoggerInterface;

class MetaFetcher
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.metaDriver)
	 */
	public function __construct(
		private MetaDriverInterface $metaDriver,
		private LoggerInterface $logger,
	) {
	}

	public function getMetas(string $url): Metadata
	{
		try {
			return $this->metaDriver->getMetas($url);
		} catch (Exception $exception) {
			$this->logger->error($exception);
		}

		return new Metadata($url);
	}
}
