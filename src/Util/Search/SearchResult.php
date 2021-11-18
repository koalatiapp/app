<?php

namespace App\Util\Search;

use Symfony\Component\Serializer\Annotation\Groups;

class SearchResult
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function __construct(
		/*
		 * @Groups({"default"})
		 */
		public string $url,
		/*
		 * @Groups({"default"})
		 */
		public string $title,
		/*
		 * @Groups({"default"})
		 */
		public ?string $snippet = null,
	) {
	}
}
