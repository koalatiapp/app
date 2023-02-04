<?php

namespace App\Util\Meta;

use Symfony\Component\Serializer\Annotation\Groups;

class Metadata
{
	#[Groups(['default'])]
	public ?string $imageUrl;

	public function __construct(
		#[Groups(['default'])]
		public string $url,
		#[Groups(['default'])]
		public ?string $siteName = null,
		#[Groups(['default'])]
		public ?string $title = null,
		#[Groups(['default'])]
		public ?string $description = null,
		?string $imageUrl = null
	) {
		$this->imageUrl = $imageUrl ?: $this->getPlaceholderImageUrl($url);
	}

	private function getPlaceholderImageUrl(string $url): string
	{
		$hostname = parse_url($url, PHP_URL_HOST);

		return "https://via.placeholder.com/600x315/DAE1FB/102984.png?text=$hostname";
	}
}
