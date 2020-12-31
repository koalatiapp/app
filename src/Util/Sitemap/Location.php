<?php

namespace App\Util\Sitemap;

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
}
