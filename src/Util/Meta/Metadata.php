<?php

namespace App\Util\Meta;

use Symfony\Component\Serializer\Annotation\Groups;

class Metadata
{
	/**
	 * @Groups({"default"})
	 */
	public string $url;

	/**
	 * @Groups({"default"})
	 */
	public ?string $siteName;

	/**
	 * @Groups({"default"})
	 */
	public ?string $title;

	/**
	 * @Groups({"default"})
	 */
	public ?string $description;

	/**
	 * @Groups({"default"})
	 */
	public ?string $imageUrl;

	public function __construct(string $url, ?string $siteName = null, ?string $title = null, ?string $description = null, ?string $imageUrl = null)
	{
		$this->url = $url;
		$this->siteName = $siteName;
		$this->title = $title;
		$this->description = $description;
		$this->imageUrl = $imageUrl;
	}
}
