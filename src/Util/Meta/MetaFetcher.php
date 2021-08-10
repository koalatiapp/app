<?php

namespace App\Util\Meta;

class MetaFetcher
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.metaDriver)
	 */
	public function __construct(
		private MetaDriverInterface $metaDriver
	) {
	}

	public function getMetas(string $url): Metadata
	{
		return $this->metaDriver->getMetas($url);
	}
}
