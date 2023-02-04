<?php

namespace App\Util\Testing;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Represents a Koalati tools-service approved automated tool.
 * The data contained by this class is expected to come direcly
 * from the NPM package of the tool.
 */
class ToolPackage
{
	#[Groups(['default'])]
	public string $url;

	public function __construct(
		#[Groups(['default'])]
		public string $name,
		#[Groups(['default'])]
		public ?string $description = null
	) {
		$this->url = 'https://www.npmjs.com/package/'.$name;
	}
}
