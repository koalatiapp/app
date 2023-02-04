<?php

namespace App\Util\Sitemap;

use DOMDocument;

/**
 * Represents a website's page or document.
 * This class is primarily used by the sitemap Builder class to hold inf.
 */
class Location
{
	public function __construct(
		/**
		 * URL of the page or document.
		 */
		public string $url,
		/**
		 * Title of the page or document.
		 */
		public ?string $title = null,
		/**
		 * HTTP status code of the page or document.
		 */
		public ?int $statusCode = null
	) {
	}

	/**
	 * @SuppressWarnings(PHPMD.ErrorControlOperator)
	 */
	public function fetchTitle(): self
	{
		if ($this->title) {
			return $this;
		}

		$context = stream_context_create([
			'http' => [
				'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36 (compatible; KoalatiBot; +https://www.koalati.com/)',
			],
		]);
		libxml_set_streams_context($context);

		$domDocument = new \DOMDocument();
		$domDocument->preserveWhiteSpace = false;

		// DOMDocument::loadHTMLFile throws uncatchable errors when it hits 403/404/500/etc.
		libxml_use_internal_errors(true);
		$documentIsLoaded = @$domDocument->loadHTMLFile($this->url);
		libxml_clear_errors();

		if ($documentIsLoaded) {
			$titleNode = $domDocument->getElementsByTagName('title');
			$title = trim($titleNode->item(0)?->textContent);

			if ($title) {
				$this->title = $title;
			}
		}

		return $this;
	}
}
