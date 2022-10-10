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
		$this->imageUrl = $imageUrl ?: $this->getPlaceholderImageUrl($url);
	}

	private function getPlaceholderImageUrl(string $url): string
	{
		$hostname = parse_url($url, PHP_URL_HOST);

		return "https://via.placeholder.com/600x315/DAE1FB/102984.png?text=$hostname";
	}
}
