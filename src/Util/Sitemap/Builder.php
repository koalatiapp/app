<?php

namespace App\Util\Sitemap;

use App\Util\Url;
use VDB\Spider\Discoverer\XPathExpressionDiscoverer;
use VDB\Spider\Event\SpiderEvents;
use VDB\Spider\EventListener\PolitenessPolicyListener;
use VDB\Spider\Filter\Prefetch\AllowedHostsFilter;
use VDB\Spider\Filter\Prefetch\UriFilter;
use VDB\Spider\Filter\Prefetch\UriWithHashFragmentFilter;
use VDB\Spider\Spider;

class Builder
{
	/**
	 * @var \App\Util\Url
	 */
	protected $urlHelper;

	/**
	 * Array of locations for the sitemap.
	 * The location's URL is used as a key to prevent duplicates.
	 *
	 * @var array<string, \App\Util\Sitemap\Location>
	 */
	protected $locations = [];

	public function __construct(Url $urlHelper)
	{
		$this->urlHelper = $urlHelper;
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
	 * If the $crawlWebsite parameter is set to true, the website will also be crawled to generate a more complete sitemap.
	 *
	 * @param string $websiteUrl   URL of the website to build the sitemap from
	 * @param bool   $crawlWebsite Whether the website should be crawled to generate a more complete sitemap. Defaults to TRUE.
	 *
	 * @return self
	 */
	public function buildFromWebsiteUrl(string $websiteUrl, bool $crawlWebsite = true)
	{
		// Standardize the provided URL
		$websiteUrl = $this->urlHelper->standardize($websiteUrl);

		// Check if a sitemap is available and scan it if possible
		if ($sitemapUrl = $this->findSitemapFromWebsiteUrl($websiteUrl)) {
			foreach ($this->scanSitemap($sitemapUrl) as $url) {
				$this->addLocation($url);
			}
		}

		// Crawl the website for a more complete sitemap (unless disabled)
		if ($crawlWebsite) {
			$this->crawlWebsite($websiteUrl);
		}

		$this->standardizeProtocols();

		return $this;
	}

	/**
	 * Finds the URL of the sitemap from the provided website URL.
	 * If no sitemap is found, or if the sitemap isn't valid, null is returned.
	 */
	protected function findSitemapFromWebsiteUrl(string $websiteUrl): ?string
	{
		$defaultSitemapUrl = $this->urlHelper->guessSitemap($websiteUrl);

		if ($this->urlHelper->exists($defaultSitemapUrl) && $this->urlHelper->isXML($defaultSitemapUrl)) {
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

		$DomDocument = new \DOMDocument();
		$DomDocument->preserveWhiteSpace = false;
		$DomDocument->load($sitemapUrl);
		$DomNodeList = $DomDocument->getElementsByTagName('loc');

		foreach ($DomNodeList as $url) {
			if (strtolower($url->tagName) == 'loc') {  // Make sure we don't get image:loc tags and stuff like that, which is frequent in Wordpress sitemaps
				if ($url->parentNode && $url->parentNode->tagName == 'sitemap') {
					foreach ($this->scanSitemap($url->nodeValue) as $childSitemapUrl) {
						$urls[] = $childSitemapUrl;
					}
				} else {
					$urls[] = $url->nodeValue;
				}
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
		$websiteUrl = $this->urlHelper->standardize($websiteUrl);
		$urls = [];
		$titlesByUrl = [];

		$spider = new Spider($websiteUrl);
		/**
		 * @var \VDB\Spider\QueueManager\InMemoryQueueManager
		 */
		$queueManager = $spider->getQueueManager();
		$spider->getDiscovererSet()->set(new XPathExpressionDiscoverer('//a'));
		$spider->getDiscovererSet()->maxDepth = 30;
		$queueManager->maxQueueSize = 1000;

		// Filter out URLs from external domains
		$spider->getDiscovererSet()->addFilter(new AllowedHostsFilter([$websiteUrl], false));

		// Filter out URLs with anchors
		$spider->getDiscovererSet()->addFilter(new UriWithHashFragmentFilter());

		// Filter out URLs that are in fact email addresses
		$spider->getDiscovererSet()->addFilter(new UriFilter(['~^(https?:)//[^/]+@[^/]+(/.*)?$~']));

		// Delay between consecutive requests
		/**
		 * @var \VDB\Spider\Downloader\Downloader
		 */
		$downloader = $spider->getDownloader();
		$politenessPolicyEventListener = new PolitenessPolicyListener(10);
		$downloader->getDispatcher()->addListener(
			SpiderEvents::SPIDER_CRAWL_PRE_REQUEST,
			[$politenessPolicyEventListener, 'onCrawlPreRequest']
		);

		try {
			// Crawl the site to look for pages
			$spider->crawl();

			// At this point, the pages are fetched, and we should have data on all of them.
			foreach ($spider->getDownloader()->getPersistenceHandler() as $resource) {
				$crawler = $resource->getCrawler();
				$url = $crawler->getUri();
				$urlWithoutTrailingSlash = preg_replace('~^(.*)/$~', '$1', $url);

				if (in_array($urlWithoutTrailingSlash, $urls)) {
					continue;
				}

				// Only add pages with an <html> tag to the list - others are likely files or images
				if (!$crawler->filterXpath('//html')->count()) {
					continue;
				}

				try {
					$title = $crawler->filterXpath('//title')->text();
				} catch (\Exception $e) {
					$title = null;
				}

				$urls[] = $url;

				if ($title && $titlesByUrl[$url] ?? null) {
					$titlesByUrl[$url] = $title;
				}
			}

			// Discard repeated URLs (ex.: both HTTP and HTTPS are present, etc.)
			foreach ($urls as $url) {
				$httpUrl = str_replace('https://', 'http://', $url);
				if ($httpUrl != $url && ($httpUrlIndex = array_search($httpUrl, $urls)) !== false) {
					unset($urls[$httpUrlIndex]);
				}
			}
		} catch (\Exception $e) {
			// Oh well...
		}

		foreach ($urls as $url) {
			$this->addLocation($url, $titlesByUrl[$url] ?? null);
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
}
