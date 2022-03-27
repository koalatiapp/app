<?php

namespace App\Util\Sitemap;

use App\Exception\CrawlingException;
use App\Util\Url;
use DOMDocument;
use Exception;
use Psr\Log\LoggerInterface;

class Builder
{
	/**
	 * Array of locations for the sitemap.
	 * The location's URL is used as a key to prevent duplicates.
	 *
	 * @var array<string, \App\Util\Sitemap\Location>
	 */
	protected array $locations = [];

	/**
	 * Whether the website should be crawled to generate a more complete sitemap.
	 */
	protected bool $shouldCrawlWebsite = true;

	public function __construct(
		private Url $urlHelper,
		private LoggerInterface $logger,
	)
	{
	}

	/**
	 * Returns the locations of the sitemap.
	 *
	 * @return array<string, \App\Util\Sitemap\Location>
	 */
	public function getLocations(): array
	{
		return $this->locations;
	}

	public function enableWebsiteCrawling(): self
	{
		$this->shouldCrawlWebsite = true;

		return $this;
	}

	public function disableWebsiteCrawling(): self
	{
		$this->shouldCrawlWebsite = false;

		return $this;
	}

	/**
	 * Builds a sitemap from a website's URL.
	 * The website's sitemap, if available, is fetched and scanned.
	 * If the builder's `crawlWebsite` property is set to true, the website will also be crawled to generate a more complete sitemap.
	 *
	 * @param string $websiteUrl URL of the website to build the sitemap from
	 *
	 * @return self
	 */
	public function buildFromWebsiteUrl(string $websiteUrl)
	{
		// Standardize the provided URL
		$websiteUrl = $this->urlHelper->standardize($websiteUrl, false);

		// Check if a sitemap is available and scan it if possible
		if ($sitemapUrl = $this->findSitemapFromWebsiteUrl($websiteUrl)) {
			foreach ($this->scanSitemap($sitemapUrl) as $url) {
				$this->addLocation($url);
			}
		}

		// Crawl the website for a more complete sitemap (unless disabled)
		if ($this->shouldCrawlWebsite) {
			try {
				$this->crawlWebsite($websiteUrl);
			} catch (CrawlingException $exception) {
				// Oh well, let's hope the sitemap was good enough...
				$this->logger->error($exception->getMessage(), $exception->getTrace());
			}
		}

		$this->standardizeProtocols();
		$this->fetchMissingTitles();

		return $this;
	}

	/**
	 * Finds the URL of the sitemap from the provided website URL.
	 * If no sitemap is found, or if the sitemap isn't valid, null is returned.
	 */
	protected function findSitemapFromWebsiteUrl(string $websiteUrl): ?string
	{
		$defaultSitemapUrl = $this->urlHelper->guessSitemap($websiteUrl);

		if ($this->urlHelper->exists($defaultSitemapUrl) && $this->urlHelper->isXml($defaultSitemapUrl)) {
			return $defaultSitemapUrl;
		}

		return null;
	}

	/**
	 * Scans the provided sitemap and returns an array containing the location URLs.
	 *
	 * @param string $sitemapUrl URL of the sitemap to scan
	 *
	 * @return array<string>
	 */
	public function scanSitemap(string $sitemapUrl): array
	{
		$urls = [];

		$domDocument = new DOMDocument();
		$domDocument->preserveWhiteSpace = false;
		$domDocument->load($sitemapUrl);
		$domNodeList = $domDocument->getElementsByTagName('loc');

		foreach ($domNodeList as $url) {
			if (strtolower($url->tagName) == 'loc') {  // Make sure we don't get image:loc tags and stuff like that, which is frequent in Wordpress sitemaps
				if ($url->parentNode && $url->parentNode->tagName == 'sitemap') {
					try {
						foreach ($this->scanSitemap($url->nodeValue) as $childSitemapUrl) {
							$urls[] = $childSitemapUrl;
						}
					} catch (Exception $exception) {
						// Sub-sitemap couldn't be fetched :/
						$this->logger->error($exception->getMessage(), $exception->getTrace());
					}

					continue;
				}

				$urls[] = $url->nodeValue;
			}
		}

		return $urls;
	}

	/**
	 * Crawls a website's pages and adds its pages to the Builder's internal locations array.
	 *
	 * @return self
	 */
	public function crawlWebsite(string $websiteUrl)
	{
		$websiteUrl = $this->urlHelper->standardize($websiteUrl, false);
		$pages = (new Crawler($websiteUrl))->crawl();

		foreach ($pages as $url => $title) {
			$this->addLocation($url, $title);
		}

		return $this;
	}

	/**
	 * Adds a location to the sitemap.
	 */
	protected function addLocation(string $url, ?string $title = null): self
	{
		if (!isset($this->locations[$url])) {
			$this->locations[$url] = new Location($url, $title);
		} elseif ($title !== null) {
			$this->locations[$url]->title = $title;
		}

		return $this;
	}

	/**
	 * Standardizes the protocol of the URLs in the sitemap.
	 * If one or more URL use HTTPS, all URLs will be converted to use it.
	 */
	protected function standardizeProtocols(): self
	{
		$useHttps = false;

		foreach ($this->locations as $url => $location) {
			if (stripos($url, 'https://') === 0) {
				$useHttps = true;
				break;
			}
		}

		if ($useHttps) {
			$newLocations = [];

			foreach ($this->locations as $url => $location) {
				$newUrl = $this->urlHelper->standardize($url, true);
				$location->url = $newUrl;
				$newLocations[$newUrl] = $location;
			}

			$this->locations = $newLocations;
		}

		return $this;
	}

	protected function fetchMissingTitles(): self
	{
		foreach ($this->locations as &$location) {
			if (!$location->title) {
				$location->fetchTitle();
			}
		}

		return $this;
	}
}
