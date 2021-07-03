<?php

namespace App\Util\Testing;

/**
 * Represents a Koalati tools-service approved automated tool.
 * The data contained by this class is expected to come direcly
 * from the NPM package of the tool.
 */
class ToolPackage
{
	public string $name;
	public string $url;
	public ?string $description;

	public function __construct(string $name, ?string $description = null)
	{
		$this->name = $name;
		$this->description = $description;
		$this->url = 'https://www.npmjs.com/package/'.$name;
	}
}
