<?php

namespace App\Util\Meta\Driver;

use App\ApiClient\Endpoint\MetasEndpoint;
use App\Util\Meta\Metadata;
use App\Util\Meta\MetaDriverInterface;

class InternalMetaApi implements MetaDriverInterface
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		private MetasEndpoint $metasEndpoint,
	) {
	}

	public function getMetas(string $url): Metadata
	{
		$metas = $this->metasEndpoint->getMetas($url);

		return new Metadata(
			$metas['url'] ?? $url,
			$metas['provider'] ?? null,
			$metas['title'] ?? null,
			$metas['description'] ?? null,
			$metas['image'] ?? null,
		);
	}
}
