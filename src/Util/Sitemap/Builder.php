<?php

namespace App\Util\Sitemap;

use App\Util\Sitemap\Exception\CrawlerException;
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
	private array $locations = [];

	public function __construct(
		private Url $urlHelper,
		private LoggerInterface $logger,
	) {
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

	/**
	 * Builds a sitemap from a website's URL.
	 * The website's sitemap, if available, is fetched and scanned.
	 * If the builder's `crawlWebsite` property is set to true, the website will also be crawled to generate a more complete sitemap.
	 *
	 * @param string   $websiteUrl        URL of the website to build the sitemap from
	 * @param callable $pageFoundCallback Callable to invoke anytime a new page is found.
	 *                                    The callback will receive a `App\Util\Sitemap\Location` argument with the page's information.
	 *
	 * @return self
	 */
	public function buildFromWebsiteUrl(string $websiteUrl, callable $pageFoundCallback)
	{
		// Standardize the provided URL
		$websiteUrl = $this->urlHelper->standardize($websiteUrl, false);

		// Check if a sitemap is available and scan it if possible
		$sitemapUrl = $this->findSitemapFromWebsiteUrl($websiteUrl);

		if ($sitemapUrl) {
			$newLocations = [];

			foreach ($this->scanSitemap($sitemapUrl) as $url) {
				$newLocations[] = new Location($url);
			}

			call_user_func($pageFoundCallback, $newLocations);
		}

		// Crawl the website for a more complete sitemap
		try {
			$crawler = new Crawler($websiteUrl, $pageFoundCallback);
			$crawler->crawl();
		} catch (CrawlerException $exception) {
			// Oh well, let's hope the sitemap was good enough...
			$this->logger->error($exception->getMessage(), $exception->getTrace());
		}

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
	 * @return array<int,string> list of URLs, sorted by length
	 */
	public function scanSitemap(string $sitemapUrl): array
	{
		$urls = [];

		$domDocument = new DOMDocument();
		$domDocument->preserveWhiteSpace = false;
		$domDocument->load($sitemapUrl);
		$domNodeList = $domDocument->getElementsByTagName('loc');

		/** @var \DOMElement $url */
		foreach ($domNodeList as $url) {
			if (strtolower($url->tagName) == 'loc') {  // Make sure we don't get image:loc tags and stuff like that, which is frequent in Wordpress sitemaps
				/** @var \DOMElement|null */
				$parentNode = $url->parentNode;

				if ($parentNode && $parentNode->tagName == 'sitemap') {
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

		// Sort URLS by length (length is a decent indicator of relevance)
		usort($urls, function (string $urlA, string $urlB) {
			return strlen($urlA) <=> strlen($urlB);
		});

		return $urls;
	}
}
