<?php

namespace App\Util\Meta;

use Symfony\Component\Serializer\Annotation\Groups;

class Metadata
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
		public ?string $title,

		/*
		 * @Groups({"default"})
		 */
		public ?string $description,

		/*
		 * @Groups({"default"})
		 */
		public ?string $imageUrl
	) {
	}
}
