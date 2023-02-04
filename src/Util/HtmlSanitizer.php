<?php

namespace App\Util;

use HtmlSanitizer\SanitizerInterface;
use Symfony\Component\Routing\RouterInterface;

class HtmlSanitizer
{
	public function __construct(
		private readonly SanitizerInterface $htmlSanitizer,
		private readonly RouterInterface $router,
	) {
	}

	public function sanitize(string $unsafeHtml): string
	{
		$sanitizedHtml = $this->htmlSanitizer->sanitize($unsafeHtml);

		return $this->proxyImages($sanitizedHtml);
	}

	private function proxyImages(string $html): string
	{
		$router = $this->router;

		return preg_replace_callback(
			'~<img ([^>]*)src=(["\'])(.+?)\2~',
			function ($matches) use ($router) {
				$proxyUrl = $router->generate(
					"image_proxy",
					[
						"url" => urlencode($matches[3]),
					],
					$router::ABSOLUTE_URL
				);

				return str_replace($matches[3], $proxyUrl, $matches[0]);
			},
			$html
		);
	}
}
