<?php

namespace App\Util\Meta;

interface MetaDriverInterface
{
	public function getMetas(string $url): Metadata;
}
