<?php

namespace App\Util\Sitemap;

use DOMDocument;

/**
 * Represents a website's page or document.
 * This class is primarily used by the sitemap Builder class to hold inf.
 */
class Location
{
	/**
	 * URL of the page or document.
	 */
	public string $url;

	/**
	 * Title of the page or document.
	 */
	public ?string $title = null;

	public function __construct(string $url, ?string $title = null)
	{
		$this->url = $url;
		$this->title = $title;
	}

	public function fetchTitle(): self
	{
		if ($this->title) {
			return $this;
		}

		$domDocument = new DOMDocument();
		$domDocument->preserveWhiteSpace = false;
		libxml_use_internal_errors(true);
		$domDocument->loadHTMLFile($this->url);
		libxml_clear_errors();
		$titleNode = $domDocument->getElementsByTagName('title');
		$title = trim($titleNode->item(0)?->textContent);

		if ($title) {
			$this->title = $title;
		}

		return $this;
	}
}
