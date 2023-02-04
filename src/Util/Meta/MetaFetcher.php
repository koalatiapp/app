<?php

namespace App\Util\Meta;

use Psr\Log\LoggerInterface;

class MetaFetcher
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.metaDriver)
	 */
	public function __construct(
		private readonly MetaDriverInterface $metaDriver,
		private readonly LoggerInterface $logger,
	) {
	}

	public function getMetas(string $url): Metadata
	{
		try {
			return $this->metaDriver->getMetas($url);
		} catch (\Exception $exception) {
			$this->logger->error($exception);
		}

		return new Metadata($url);
	}
}
