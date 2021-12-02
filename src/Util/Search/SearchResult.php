<?php

namespace App\Util\Search;

use Symfony\Component\Serializer\Annotation\Groups;

class SearchResult
{
	/**
	 * @Groups({"default"})
	 */
	public string $url;

	/**
	 * @Groups({"default"})
	 */
	public string $title;

	/**
	 * @Groups({"default"})
	 */
	public ?string $snippet;

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(string $url, string $title, ?string $snippet = null)
	{
		$this->url = $url;
		$this->title = $title;
		$this->snippet = $snippet;
	}
}
