<?php

namespace App\Util\Meta\Driver;

use App\Util\Meta\Metadata;
use App\Util\Meta\MetaDriverInterface;

class MockMetaDriver implements MetaDriverInterface
{
	public function getMetas(string $url): Metadata
	{
		return new Metadata(
			$url,
			'Website',
			'Page title',
			'Meta description',
			null
		);
	}
}
